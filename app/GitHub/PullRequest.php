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
            $this->reviewRequests->push(new RequestedReview($this, $reviewRequest));
        }

        $reviews = collect($data['reviews']['nodes'])->mapWithKeys(function ($review) {
            return [$review['author']['login'] => $review];
        })->filter(function ($review) {
            return Carbon::parse($review['submittedAt'])->gte(Carbon::now()->previousWeekday());
        })->sortBy(function ($review) {
            return Carbon::parse($review['submittedAt']);
        });
        foreach ($reviews as $review) {
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

    public function getReviews(): Collection
    {
        return $this->reviews;
    }
}
