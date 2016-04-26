<?php

namespace Wizard\Src\Modules\Exception;

use Wizard\Src\Exception\WizardRuntimeException;

class DebuggerException extends WizardRuntimeException implements DebuggerExceptionInterface
{

    function __construct($message, $solution = null)
    {
        parent::__construct($message, $solution, 'DebuggerException');
    }

}