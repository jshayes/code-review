<?php

namespace App\GitHub;

use Carbon\Carbon;
use Github\ResultPager;
use Illuminate\Support\Collection;

class Repository
{
    public function __construct(Organization $organization, array $data)
    {
        $this->organization = $organization;
        $this->name = $data['name'];
        $this->pullRequests = collect();

        foreach ($data['pullRequests']['nodes'] as $pullRequest) {
            $this->pullRequests->push(new PullRequest($this, $pullRequest));
        }
    }

    public function getOrganization(): Organization
    {
        return $this->organization;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPullRequests(): Collection
    {
        return $this->pullRequests;
    }
}
