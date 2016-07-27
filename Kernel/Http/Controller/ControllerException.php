<?php

namespace Wizard\Kernel\Http\Controller;

use Wizard\Exception\WizardRuntimeException;

class ControllerException extends WizardRuntimeException
{

    function __construct($message, $solution = null)
    {
        parent::__construct($message, $solution, 'ControllerException');
    }

}