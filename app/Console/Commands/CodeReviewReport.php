<?php

namespace App\Console\Commands;

use App\User;
use App\Report;
use Carbon\Carbon;
use App\GithubUser;
use App\GitHub\Client;
use Github\ResultPager;
use App\GitHub\Organization;
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

    private $names = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function convertToName($login)
    {
        if (!array_key_exists($login, $this->names)) {
            $client = new Client();
            $user = $client->api('user')->show($login);

            $this->names[$login] = $user['name'] ?: $login;
        }

        return $this->names[$login];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $time = Carbon::now()->subMonth(2)->startOfDay();
        $lastRanAt = Carbon::now()->previousWeekday();
        $client = new Client();
        $pager = new ResultPager($client);

        $report = new Report();
        $organization = new Organization('SoapBox');

        $organization->getRepositories()->filter(function ($repository) use ($time) {
            return $time->lte($repository->getPushedAt());
        })->each(function ($repository) use ($report, $lastRanAt) {
            $repository->getOpenPullRequests()
                ->each(function ($pullRequest) use ($report, $lastRanAt) {
                    $requestedReviews = $pullRequest->getRequestedReviews();

                    if (!$requestedReviews->isEmpty()) {
                        $requestedReviews->each(function ($requestedReviews) use ($report) {
                            $report->addRequestedReview($requestedReviews);
                        });
                    } else if ($lastRanAt->lte($pullRequest->getUpdatedAt())) {
                        $latestReview = $pullRequest->getReviews()->filter(function ($review) use ($lastRanAt) {
                            return $lastRanAt->lte($review->getSubmittedAt()) && !in_array($review->getState(), ['PENDING', 'COMMENT']);
                        })->last();

                        if (!is_null($latestReview)) {
                            $report->addReview($latestReview);
                        }
                    }
                });
        });

        $user = new User();
        $user->email = env('REPORT_EMAIL');
        $user->notify(new CodeReview($report));
    }
}
