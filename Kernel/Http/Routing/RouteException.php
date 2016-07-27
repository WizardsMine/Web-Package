<?php

namespace Wizard\Kernel\Http\Routing;

use Wizard\Exception\WizardRuntimeException;

class RouteException extends WizardRuntimeException
{

    function __construct($message, $solution = null)
    {
        parent::__construct($message, $solution, 'RouteException');
    }

}