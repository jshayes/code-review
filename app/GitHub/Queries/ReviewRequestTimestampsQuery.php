<?php

namespace App\GitHub\Queries;

class ReviewRequestTimestampsQuery
{
    public function __toString(): string
    {
        return file_get_contents(resource_path('queries/review-request-timestamps'));
    }
}
