<?php

namespace App\GitHub;

use Github\ResultPager;
use Illuminate\Support\Collection;

class Organization
{
    public function __construct(array $data)
    {
        $this->members = collect();
        $this->repositories = collect();

        foreach ($data['members']['nodes'] as $member) {
            $member = new User($member);
            $this->members->put($member->getLogin(), $member);
        }

        foreach ($data['repositories']['nodes'] as $repository) {
            $this->repositories->push(new Repository($this, $repository));
        }
    }

    public function getRepositories(): Collection
    {
        return $this->repositories;
    }

    public function getMember(string $login): User
    {
        return $this->members->get($login, new User(['login' => $login]));
    }
}
