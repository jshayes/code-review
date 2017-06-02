<?php

namespace App;

use App\GitHub\RequestedReview;
use Illuminate\Support\Collection;
use App\GitHub\UserNameTransformer;

class Report
{
    private $requestedReviews;

    public function __construct()
    {
        $this->requestedReviews = new Collection();
    }

    public function addRequestedReview(RequestedReview $requestedReview)
    {
        $this->requestedReviews->push($requestedReview);
    }

    public function toArray()
    {
        $userNameTransformer = new UserNameTransformer();

        return [
            'requested_reviews' => $this->requestedReviews->groupBy(function ($requestedReview) {
                return $requestedReview->getReviewer()->getLogin();
            })->map(function ($requestedReviews, $logon) use ($userNameTransformer) {
                return [
                    'reviewer_name' => $userNameTransformer->getUserName($requestedReviews->first()->getReviewer()),
                    'pull_requests' => $requestedReviews->map(function ($requestedReview) use ($userNameTransformer) {
                        $pullRequest = $requestedReview->getPullRequest();
                        return [
                            'author_name' => $userNameTransformer->getUserName($pullRequest->getAuthor()),
                            'title' => $pullRequest->getTitle(),
                            'url' => $pullRequest->getUrl(),
                            'repository_name' => $pullRequest->getRepository()->getName(),
                        ];
                    })
                ];
            })
        ];
    }
}
