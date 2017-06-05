<?php

namespace App\GitHub;

class RequestedReview
{
    private $pullRequest;
    private $reviewer;

    public function __construct(PullRequest $pullRequest, User $reviewer)
    {
        $this->pullRequest = $pullRequest;
        $this->reviewer = $reviewer;
    }

    public function getPullRequest(): PullRequest
    {
        return $this->pullRequest;
    }

    public function getReviewer(): User
    {
        return $this->reviewer;
    }
}
