<?php

namespace App\GitHub;

use Carbon\Carbon;
use Github\ResultPager;
use Illuminate\Support\Collection;

class Repository
{
    private $organization;
    private $data;

    public function __construct(Organization $organization, array $data)
    {
        $this->organization = $organization;
        $this->data = $data;
    }

    public function getOrganization(): Organization
    {
        return $this->organization;
    }

    public function getName(): string
    {
        return $this->data['name'];
    }

    public function getPushedAt(): Carbon
    {
        return Carbon::parse($this->data['pushed_at']);
    }

    public function getOpenPullRequests(): Collection
    {
        $client = new Client();
        $pager = new ResultPager($client);

        return (new Collection(
            $pager->fetchAll(
                $client->api('pull_request'),
                'all',
                [
                    $this->organization->getName(),
                    $this->getName(),
                    ['state' => 'open'],
                ]
            )
        ))->map(function ($pullRequest) {
            return new PullRequest($this, $pullRequest);
        });
    }
}
