<?php

namespace App\GitHub;

use Github\HttpClient\Builder;
use Github\Client as GitHubClient;

class Client extends GitHubClient
{
    public function __construct(Builder $httpClientBuilder = null, $apiVersion = null, $enterpriseUrl = null)
    {
        parent::__construct($httpClientBuilder, $apiVersion, $enterpriseUrl);
        $this->authenticate(env('GITHUB_TOKEN'), GitHubClient::AUTH_HTTP_TOKEN);
    }
}
