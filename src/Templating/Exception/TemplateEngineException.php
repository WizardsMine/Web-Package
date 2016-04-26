<?php

namespace Wizard\Src\Templating\Exception;

use Wizard\Src\Exception\WizardRuntimeException;

class TemplateEngineException extends WizardRuntimeException
{
    function __construct($message = null, $solution = null)
    {
        parent::__construct($message, $solution, 'TemplateEngineException');
    }
}