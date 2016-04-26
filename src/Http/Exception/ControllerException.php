<?php

namespace Wizard\Src\Http\Exception;

use Wizard\Src\Exception\WizardRuntimeException;

class ControllerException extends WizardRuntimeException implements ControllerExceptionInterface
{

    function __construct($message, $solution = null)
    {
        parent::__construct($message, $solution, 'ControllerException');
    }

}