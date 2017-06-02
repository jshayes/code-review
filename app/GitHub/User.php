<?php

namespace App\GitHub;

class User
{
    private $user;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getLogin(): string
    {
        return $this->data['login'];
    }
}
