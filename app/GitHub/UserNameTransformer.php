<?php

namespace App\GitHub;

use Illuminate\Support\Collection;

class UserNameTransformer
{
    private static $instance;
    private $names;

    private function __construct()
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

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new UserNameTransformer();
        }

        return self::$instance;
    }
}
