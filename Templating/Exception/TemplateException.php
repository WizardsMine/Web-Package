<?php

namespace Wizard\Templating\Exception;

use Wizard\Exception\WizardRuntimeException;

class TemplateException extends WizardRuntimeException
{
    function __construct($message = null, $solution = null)
    {
        parent::__construct($message, $solution, 'TemplateException');
    }
}