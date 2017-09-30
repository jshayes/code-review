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
        $this->author = $this->getOrganization()->getMember($data['author']['login']);
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
}
