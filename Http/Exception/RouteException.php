<?php

namespace Wizard\Http\Exception;

use Wizard\Exception\WizardRuntimeException;

class RouteException extends WizardRuntimeException implements RouteExceptionInterface
{

    function __construct($message, $solution = null)
    {
        parent::__construct($message, $solution, 'RouteException');
    }

}