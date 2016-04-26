<?php

namespace Wizard\Src\Modules\Debugger;

class Debugger
{
    public static function debug(string $message = '')
    {
        debug_backtrace();
    }
}