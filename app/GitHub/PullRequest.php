<?php

namespace App\GitHub;

use Carbon\Carbon;
use Github\ResultPager;
use Illuminate\Support\Collection;

class PullRequest
{
    public function __construct(Repository $repository, array $data)
    {
        $this->repository = $repository;
        $this->id = $data['id'];
        $this->title = $data['title'];
        $this->url = $data['url'];
        $this->author = $this->getOrganization()->getMember($data['author']['login']);
        $this->reviewRequests = collect();
        $this->reviews = collect();

        foreach ($data['reviewRequests']['nodes'] as $reviewRequest) {
            if (isset($reviewRequest['reviewer']['login'])) {
                $this->reviewRequests->push(new RequestedReview($this, $reviewRequest));
            }
        }

        foreach ($data['reviews']['nodes'] as $review) {
            $this->reviews->push(new Review($this, $review));
        }
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }

    public function getOrganization(): Organization
    {
        return $this->getRepository()->getOrganization();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function getReviewRequests(): Collection
    {
        return $this->reviewRequests;
    }

    public function hasReviewRequests(): bool
    {
        return $this->reviewRequests->isNotEmpty();
    }

    public function hasReviews(): bool
    {
        return $this->reviews->isNotEmpty();
    }

    public function getReviewState(): ?string
    {
        $reviews = $this->reviews->mapWithKeys(function ($review) {
            return [$review->getAuthor()->getLogin() => $review->getState()];
        });

        if ($reviews->contains('CHANGES_REQUESTED')) {
            return 'CHANGES_REQUESTED';
        } else if ($reviews->contains('APPROVED')) {
            return 'APPROVED';
        }

        return null;
    }
}
