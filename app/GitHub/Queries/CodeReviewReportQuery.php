<?php

namespace App\GitHub\Queries;

class CodeReviewReportQuery
{
    public function __toString(): string
    {
        return file_get_contents(resource_path('queries/code-review-report'));
    }
}
