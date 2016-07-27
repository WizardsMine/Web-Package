<?php

namespace Wizard\Modules\Sessions;

class Session
{

    public function get(string $key)
    {
        return $_SESSION[$key] ?? null;
    }

    public function put(string $key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function unset(string $key)
    {
        unset($_SESSION[$key]);
    }

}
