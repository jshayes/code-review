<?php

namespace App\GitHub;

class User
{
    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->login = $data['login'];
        $this->name = $data['name'] ?? null ?: $this->login;
    }

    public function getId() : ?string
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
