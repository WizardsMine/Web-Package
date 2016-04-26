<?php

namespace Wizard\Src\Modules\Exception;

use Wizard\Src\Exception\WizardRuntimeException;

class DatabaseException extends WizardRuntimeException
{

    function __construct($message, $solution = null)
    {
        parent::__construct($message, $solution, 'DatabaseException');
    }

}