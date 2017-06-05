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
        $this->data = $data;
    }

    public function getPullRequest(): PullRequest
    {
        return $this->pullRequest;
    }

    public function getReviewer(): User
    {
        return new User($this->data['user']);
    }

    public function getState(): string
    {
        return $this->data['state'];
    }

    public function getStateString(): string
    {
        switch ($this->getState()) {
            case 'APPROVED':
                return 'approved';
                break;

            case 'CHANGES_REQUESTED':
                return 'requested changes to';
                break;

            default:
                return 'reviewed';
                break;
        }
    }

    public function getSubmittedAt(): Carbon
    {
        return Carbon::parse($this->data['submitted_at']);
    }
}
