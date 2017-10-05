<?php

namespace App;

use Carbon\Carbon;
use App\GitHub\Review;
use Illuminate\Support\Str;
use App\GitHub\Organization;
use App\GitHub\RequestedReview;
use Illuminate\Support\Collection;
use App\GitHub\UserNameTransformer;
use App\RequestedReview as RequestedReviewModel;

class Report
{
    private $requestedReviews;
    private $reviews;

    public function __construct(Organization $org)
    {
        $this->requestedReviews = new Collection();
        $this->reviews = new Collection();
        $this->reviewedPrs = new Collection();

        foreach ($org->getRepositories() as $repo) {
            foreach ($repo->getPullRequests() as $pr) {
                foreach ($pr->getReviewRequests() as $request) {
                    $this->addRequestedReview($request);
                }

                if (!$pr->hasReviewRequests() && $pr->hasReviews() && $pr->getReviewState()) {
                    $this->reviewedPrs->push($pr);
                }
            }
        }
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
                                'avatar' => $pullRequest->getAuthor()->getAvatar(),
                                'title' => $pullRequest->getTitle(),
                                'url' => $pullRequest->getUrl(),
                                'repository_name' => $pullRequest->getRepository()->getName(),
                                'days' => $days ?? '',
                            ];
                        })
                    ];
                }),
            'reviews' => $this->reviewedPrs
                ->groupBy(function ($pr) {
                    return $pr->getAuthor()->getName();
                })
                ->sortBy(function ($prs) {
                    return $prs->first()->getAuthor()->getName();
                })
                ->map(function ($prs, $login) {
                    return [
                        'author_name' => $prs->first()->getAuthor()->getName(),
                        'pull_requests' => $prs->map(function ($pr) {
                            return [
                                'state' => $pr->getReviewState(),
                                'title' => $pr->getTitle(),
                                'url' => $pr->getUrl(),
                                'repository_name' => $pr->getRepository()->getName(),
                            ];
                        }),
                    ];
                }),
        ];
    }
}
