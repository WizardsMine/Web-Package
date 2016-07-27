<?php

namespace Wizard\Kernel\Http\Middleware;

use Wizard\Exception\WizardRuntimeException;

class MiddlewareException extends WizardRuntimeException
{

    function __construct($message, $solution = null)
    {
        parent::__construct($message, $solution, 'MiddlewareException');
    }

}