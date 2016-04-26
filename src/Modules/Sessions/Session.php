<?php

namespace Wizard\Src\Modules\Sessions;

class Session
{
    public $session;

    public function getSession()
    {
        return "Wow";
    }

    public function setSession($value)
    {
        $this->session = $value;
    }

    public function get(string $key)
    {
        return $_SESSION[$key] ?? '';
    }

    public function put(string $key, $value)
    {
        $_SESSION[$key] = $value;
    }

}