<?php

namespace App\GitHub;

use Carbon\Carbon;

class Review
{
    private $pullRequest;
    private $data;

    public function __construct(PullRequest $pullRequest, array $data)
    {
        $this->pullRequest = $pullRequest;
        $this->state = $data['state'];
        $this->author = new User($data['author']);
    }

    public function getPullRequest(): PullRequest
    {
        return $this->pullRequest;
    }

    public function getOrganization(): Organization
    {
        return $this->getPullRequest()->getOrganization();
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function getState(): string
    {
        return $this->state;
    }
}
