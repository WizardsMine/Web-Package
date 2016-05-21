<?php

namespace Wizard\Modules\Exception;

use Wizard\Exception\WizardRuntimeException;

class DebuggerException extends WizardRuntimeException implements DebuggerExceptionInterface
{

    function __construct($message, $solution = null)
    {
        parent::__construct($message, $solution, 'DebuggerException');
    }

}