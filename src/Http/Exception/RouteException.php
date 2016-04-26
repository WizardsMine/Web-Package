<?php

namespace Wizard\Src\Http\Exception;

use Wizard\Src\Exception\WizardRuntimeException;

class RouteException extends WizardRuntimeException implements RouteExceptionInterface
{

    function __construct($message, $solution = null)
    {
        parent::__construct($message, $solution, 'RouteException');
    }

}