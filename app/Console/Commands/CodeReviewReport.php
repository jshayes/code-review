<?php

namespace App\Console\Commands;

use App\User;
use Exception;
use App\Report;
use Carbon\Carbon;
use App\GithubUser;
use App\GitHub\Client;
use Github\ResultPager;
use App\GitHub\Organization;
use Illuminate\Console\Command;
use App\Notifications\CodeReview;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\GitHub\Queries\CodeReviewReportQuery;

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
        try {
            $this->call('code-review:update-timestamps', ['minutes' => 5]);
        } catch (Exception $e) {
            Log::error($e);
        }

        $report = new Report();
        $client = new Client();
        $response = $client->api('graphql')->execute((string) new CodeReviewReportQuery());

        $org = new Organization($response['data']['organization']);
        foreach ($org->getRepositories() as $repo) {
            foreach ($repo->getPullRequests() as $pr) {
                foreach ($pr->getReviewRequests() as $request) {
                    $report->addRequestedReview($request);
                }

                if (!$pr->hasReviewRequests()) {
                    foreach ($pr->getReviews() as $review) {
                        $report->addReview($review);
                    }
                }
            }
        }

        $user = new User();
        $user->email = env('REPORT_EMAIL');
        $user->notify(new CodeReview($report));
    }
}
