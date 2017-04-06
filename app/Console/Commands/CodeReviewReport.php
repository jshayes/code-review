<?php

namespace App\Console\Commands;

use App\User;
use Carbon\Carbon;
use Github\Client;
use Github\ResultPager;
use Illuminate\Console\Command;
use App\Notifications\CodeReview;
use Illuminate\Support\Collection;

class CodeReviewReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code-review:send-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends the daily code review report.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $time = Carbon::now()->subMonth(2)->startOfDay();
        $client = new Client();
        $client->authenticate(env('GITHUB_TOKEN'), Client::AUTH_HTTP_TOKEN);
        $pager = new ResultPager($client);

        $repositories = (new Collection(
            $pager->fetchAll($client->api('repo'), 'org', ['SoapBox', ['type' => 'private']])
        ))->filter(function ($repository) use ($time) {
            return $time->lte(Carbon::parse($repository['pushed_at']));
        });

        $reviewers = new Collection();

        foreach ($repositories as $repository) {
            $pullRequests = $pager->fetchAll(
                $client->api('pull_request'),
                'all',
                [
                    'SoapBox',
                    $repository['name'],
                    ['state' => 'open'],
                ]
            );

            foreach ($pullRequests as $pullRequest) {
                $requestedReviewers = $pager->fetchAll(
                    $client->api('pull_request')->reviewRequests()->configure(),
                    'all',
                    [
                        'SoapBox',
                        $repository['name'],
                        $pullRequest['number'],
                    ]
                );

                foreach ($requestedReviewers as $requestedReviewer) {
                    if (!$reviewers->has($requestedReviewer['login'])) {
                        $reviewers->put($requestedReviewer['login'], new Collection());
                    }

                    $reviewers->get($requestedReviewer['login'])->push($pullRequest);
                }
            }
        }

        $user = new User();
        $user->email = env('REPORT_EMAIL');
        $user->notify(new CodeReview($reviewers));
    }
}
