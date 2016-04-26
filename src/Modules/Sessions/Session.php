<?php

namespace Wizard\Src\Modules\Sessions;

class Session
{

    public function get(string $key)
    {
        return $_SESSION[$key] ?? '';
    }

    public function put(string $key, $value)
    {
        $_SESSION[$key] = $value;
    }

}
