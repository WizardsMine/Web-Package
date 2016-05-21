<?php

namespace Wizard\Http\Exception;

use Wizard\Exception\WizardRuntimeException;

class ControllerException extends WizardRuntimeException implements ControllerExceptionInterface
{

    function __construct($message, $solution = null)
    {
        parent::__construct($message, $solution, 'ControllerException');
    }

}