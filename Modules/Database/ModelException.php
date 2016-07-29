<?php

namespace Wizard\Modules\Database;

use Wizard\Exception\WizardRuntimeException;

class ModelException extends WizardRuntimeException
{

    function __construct($message, $solution = null)
    {
        parent::__construct($message, $solution, 'ModelException');
    }

}