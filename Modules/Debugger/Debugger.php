<?php

namespace Wizard\Modules\Debugger;

class Debugger
{
    //TODO debugger
    public static function debug(string $message = '')
    {
        debug_backtrace();
    }
}