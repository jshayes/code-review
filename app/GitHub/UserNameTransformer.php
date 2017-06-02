<?php

namespace App\GitHub;

use Illuminate\Support\Collection;

class UserNameTransformer
{
    private $names;

    public function __construct()
    {
        $this->names = new Collection();
    }

    public function getUserName(User $user)
    {
        $login = $user->getLogin();

        if (!$this->names->has($login)) {
            $client = new Client();
            $user = $client->api('user')->show($login);

            $this->names->put($login, $user['name'] ?: $login);
        }

        return $this->names->get($login);
    }
}
