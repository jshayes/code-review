<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\GitHub\RequestedReview as ReviewRequest;

class RequestedReview extends Model
{
    protected $guarded = [];
    protected $dates = ['requested_at'];

    public static function findByReviewRequest(ReviewRequest $request)
    {
        return self::where('pull_request_id', $request->getPullRequest()->getId())
            ->where('reviewer_id', $request->getReviewer()->getId())
            ->first();
    }
}
