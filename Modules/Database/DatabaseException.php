<?php

namespace Wizard\Modules\Database;

use Wizard\Exception\WizardRuntimeException;

class DatabaseException extends WizardRuntimeException
{

    function __construct($message, $solution = null)
    {
        parent::__construct($message, $solution, 'DatabaseException');
    }

}