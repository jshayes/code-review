<?php

namespace App\GitHub;

use Github\ResultPager;
use Illuminate\Support\Collection;

class Organization
{
    private $organization;

    public function __construct(string $organization)
    {
        $this->organization = $organization;
    }

    public function getName(): string
    {
        return $this->organization;
    }

    public function getRepositories(): Collection
    {
        $client = new Client();
        $pager = new ResultPager($client);

        return (new Collection(
            $pager->fetchAll($client->api('repo'), 'org', [$this->organization])
        ))->map(function ($repository) {
            return new Repository($this, $repository);
        });
    }
}
