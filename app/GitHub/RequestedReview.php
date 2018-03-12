<?php

namespace App\GitHub;

class RequestedReview
{
    private $pullRequest;
    private $reviewer;

    public function __construct(PullRequest $pullRequest, array $data)
    {
        $this->pullRequest = $pullRequest;
        $this->reviewer = new User($data['requestedReviewer']);
    }

    public function getPullRequest(): PullRequest
    {
        return $this->pullRequest;
    }

    public function getOrganization(): Organization
    {
        return $this->getPullRequest()->getOrganization();
    }

    public function getReviewer(): User
    {
        return $this->reviewer;
    }
}
