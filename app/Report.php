<?php

namespace App;

use Carbon\Carbon;
use App\GitHub\Review;
use Illuminate\Support\Str;
use App\GitHub\RequestedReview;
use Illuminate\Support\Collection;
use App\GitHub\UserNameTransformer;
use App\RequestedReview as RequestedReviewModel;

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
                ->sortBy(function ($requestedReview) {
                    return $requestedReview->getPullRequest()->getAuthor()->getName();
                })
                ->groupBy(function ($requestedReview) {
                    return $requestedReview->getReviewer()->getName();
                })
                ->sortBy(function ($requestedReview) {
                    return $requestedReview->first()->getReviewer()->getName();
                })
                ->map(function ($requestedReviews, $logon) {
                    return [
                        'reviewer_name' => $requestedReviews->first()->getReviewer()->getName(),
                        'pull_requests' => $requestedReviews->map(function ($requestedReview) {
                            $model = RequestedReviewModel::findByReviewRequest($requestedReview);
                            if (!is_null($model)) {
                                $days = $model->requested_at->diffForHumans();
                            }
                            $pullRequest = $requestedReview->getPullRequest();
                            return [
                                'author_name' => $pullRequest->getAuthor()->getName(),
                                'title' => $pullRequest->getTitle(),
                                'url' => $pullRequest->getUrl(),
                                'repository_name' => $pullRequest->getRepository()->getName(),
                                'days' => $days ?? '',
                            ];
                        })
                    ];
                }),
            'reviews' => $this->reviews
                ->sortBy(function ($review) {
                    return $review->getAuthor()->getName();
                })
                ->groupBy(function ($review) {
                    return $review->getPullRequest()->getAuthor()->getName();
                })
                ->sortBy(function ($review) {
                    return $review->first()->getPullRequest()->getAuthor()->getName();
                })
                ->map(function ($reviews, $login) {
                    return [
                        'author_name' => $reviews->first()->getPullRequest()->getAuthor()->getName(),
                        'pull_requests' => $reviews->map(function ($review) {
                            return [
                                'reviewer_name' => $review->getAuthor()->getName(),
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
