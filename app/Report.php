<?php

namespace App;

use App\GitHub\Review;
use App\GitHub\RequestedReview;
use Illuminate\Support\Collection;
use App\GitHub\UserNameTransformer;

class Report
{
    private $requestedReviews;
    private $reviews;

    public function __construct()
    {
        $this->requestedReviews = new Collection();
        $this->reviews = new Collection();
    }

    public function addRequestedReview(RequestedReview $requestedReview)
    {
        $this->requestedReviews->push($requestedReview);
    }

    public function addReview(Review $reviews)
    {
        $this->reviews->push($reviews);
    }

    public function toArray()
    {
        return [
            'requested_reviews' => $this->requestedReviews
                ->groupBy(function ($requestedReview) {
                    return $requestedReview->getReviewer()->getLogin();
                })
                ->sort(function ($a, $b) {
                    $aName = $a->first()->getReviewer()->getName();
                    $bName = $b->first()->getReviewer()->getName();

                    return strcmp(strtolower($aName), strtolower($bName));
                })
                ->map(function ($requestedReviews, $logon) {
                    return [
                        'reviewer_name' => $requestedReviews->first()->getReviewer()->getName(),
                        'pull_requests' => $requestedReviews->map(function ($requestedReview) {
                            $pullRequest = $requestedReview->getPullRequest();
                            return [
                                'author_name' => $pullRequest->getAuthor()->getName(),
                                'title' => $pullRequest->getTitle(),
                                'url' => $pullRequest->getUrl(),
                                'repository_name' => $pullRequest->getRepository()->getName(),
                            ];
                        })
                    ];
                }),
            'reviews' => $this->reviews
                ->groupBy(function ($review) {
                    return $review->getPullRequest()->getAuthor()->getLogin();
                })
                ->sort(function ($a, $b) {
                    $aName = $a->first()->getPullRequest()->getAuthor()->getName();
                    $bName = $b->first()->getPullRequest()->getAuthor()->getName();

                    return strcmp(strtolower($aName), strtolower($bName));
                })
                ->map(function ($reviews, $login) {
                    return [
                        'author_name' => $reviews->first()->getPullRequest()->getAuthor()->getName(),
                        'pull_requests' => $reviews->map(function ($review) {
                            return [
                                'reviewer_name' => $review->getReviewer()->getName(),
                                'status' => $review->getStateString(),
                                'title' => $review->getPullRequest()->getTitle(),
                                'url' => $review->getPullRequest()->getUrl(),
                                'repository_name' => $review->getPullRequest()->getRepository()->getName(),
                            ];
                        }),
                    ];
                }),
        ];
    }
}
