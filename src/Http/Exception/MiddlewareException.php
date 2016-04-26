<?php

namespace Wizard\Src\Http\Exception;

use Wizard\Src\Exception\WizardRuntimeException;

class MiddlewareException extends WizardRuntimeException
{

    function __construct($message, $solution = null)
    {
        parent::__construct($message, $solution, 'MiddlewareException');
    }

}