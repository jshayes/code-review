<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\GitHub\Client;
use App\RequestedReview;
use Illuminate\Console\Command;
use App\GitHub\Queries\ReviewRequestTimestampsQuery;

class ReviewRequestTimestamps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code-review:update-timestamps {minutes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = new Client();
        $response = $client->api('graphql')->execute(
            (string) new ReviewRequestTimestampsQuery(),
            ['after' => Carbon::now()->subMinutes($this->argument('minutes'))->toAtomString()]
        );

        $repos = collect($response['data']['organization']['repositories']['nodes']);
        $events = [];
        foreach ($repos as $repo) {
            foreach ($repo['pullRequests']['nodes'] as $pr) {
                foreach ($pr['timeline']['nodes'] as $event) {
                    $event['pr_id'] = $pr['id'];
                    $events[] = $event;
                }
            }
        }

        $events = collect($events)->filter(function ($event) {
            return in_array($event['__typename'], ['ReviewRequestedEvent', 'ReviewRequestRemovedEvent']);
        })->sortBy(function ($event) {
            $time = Carbon::parse($event['createdAt']);
            return $time;
        });

        foreach ($events as $event) {
            if (!($event['requestedReviewer']['id'] ?? null)) {
                continue;
            }

            if ($event['__typename'] == 'ReviewRequestedEvent') {
                $model = RequestedReview::firstOrCreate(
                    ['pull_request_id' => $event['pr_id'], 'reviewer_id' => $event['requestedReviewer']['id']],
                    ['pull_request_id' => $event['pr_id'], 'reviewer_id' => $event['requestedReviewer']['id'], 'requested_at' => Carbon::parse($event['createdAt'])]
                );
                $createdAt = Carbon::parse($event['createdAt']);
                if ($createdAt->gt($model->requested_at)) {
                    $model->requested_at = $createdAt;
                    $model->save();
                }
            } elseif ($event['__typename'] == 'ReviewRequestRemovedEvent') {
                $model = RequestedReview::where('pull_request_id', $event['pr_id'])
                    ->where('reviewer_id', $event['requestedReviewer']['id'])
                    ->first();

                if (!is_null($model) && Carbon::parse($event['createdAt'])->gte($model->requested_at)) {
                    $model->delete();
                }
            }
        }
    }
}
