<?php

namespace Wizard\Kernel\Http;

use Wizard\Exception\WizardRuntimeException;

class HttpKernelException extends WizardRuntimeException
{

    function __construct($message, $solution = null)
    {
        parent::__construct($message, $solution, 'HttpKernelException');
    }

}