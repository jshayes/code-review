<?php

use App\User;
use Carbon\Carbon;
use Github\Client;
use Github\ResultPager;
use App\Notifications\CodeReview;
use Illuminate\Support\Collection;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $time = Carbon::now()->subMonth(2)->startOfDay();
    $client = new Client();
    $client->authenticate(env('GITHUB_TOKEN'), Client::AUTH_HTTP_TOKEN);
    $pager = new ResultPager($client);
    $repositories = (new Collection(
        $pager->fetchAll($client->api('repo'), 'org', ['SoapBox', ['type' => 'private']])
    ))->filter(function ($repository) use ($time) {
        return $time->lte(Carbon::parse($repository['pushed_at']));
    })->sort(function ($repository) {
        return Carbon::parse($repository['pushed_at'])->getTimestamp() * -1;
    })->map(function ($repository) use ($client, $pager) {
        $repository['pull_requests'] = (new Collection($pager->fetchAll(
            $client->api('pull_request'),
            'all',
            [
                'SoapBox',
                $repository['name'],
                ['state' => 'open'],
            ]
        )))->map(function ($pullRequest) use ($client, $pager, $repository) {
            $pullRequest['reviews_requested'] = new Collection(
                $pager->fetchAll(
                    $client->api('pull_request')->reviewRequests()->configure(),
                    'all',
                    [
                        'SoapBox',
                        $repository['name'],
                        $pullRequest['number'],
                    ]
                )
            );

            return $pullRequest;
        })->filter(function ($pullRequest) {
            return !$pullRequest['reviews_requested']->isEmpty();
        });

        return $repository;
    })->filter(function ($repository) {
        return !$repository['pull_requests']->isEmpty();
    });

    $reviews = new Collection();

    foreach ($repositories as $repository) {
        foreach ($repository['pull_requests'] as $pullRequest) {
            foreach ($pullRequest['reviews_requested'] as $reviewer) {
                if (!$reviews->has($reviewer['login'])) {
                    $reviews->put($reviewer['login'], new Collection());
                }

                $reviews->get($reviewer['login'])->push($pullRequest);
            }
        }
    }

    $user = new User();
    $user->email = 'jshfish@gmail.com';
    $user->notify(new CodeReview($reviews));
});
