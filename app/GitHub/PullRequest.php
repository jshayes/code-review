<?php

namespace App\GitHub;

use Github\ResultPager;
use Illuminate\Support\Collection;

class PullRequest
{
    private $repository;
    private $data;

    public function __construct(Repository $repository, array $data)
    {
        $this->repository = $repository;
        $this->data = $data;
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }

    public function getNumber(): int
    {
        return (int) $this->data['number'];
    }

    public function getAuthor(): User
    {
        return new User($this->data['user']);
    }

    public function getTitle(): string
    {
        return $this->data['title'];
    }

    public function getUrl(): string
    {
        return $this->data['html_url'];
    }

    public function getRequestedReviews(): Collection
    {
        $reviewers = [];
        if (array_key_exists('requested_reviewers', $this->data)) {
            $reviewers = $this->data['requested_reviewers'];
        } else {
            $client = new Client();
            $pager = new ResultPager($client);
            $reviewers = $pager->fetchAll(
                $client->api('pull_request')->reviewRequests()->configure(),
                'all',
                [
                    $this->repository->getOrganization()->getName(),
                    $this->repository->getName(),
                    $this->getNumber(),
                ]
            );
        }

        return (new Collection($reviewers))->map(function ($user) {
            return new RequestedReview($this, new User($user));
        });
    }
}
