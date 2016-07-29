<?php

namespace Wizard\Assets;

use Wizard\Exception\WizardRuntimeException;

class AssetException extends WizardRuntimeException
{

    function __construct($message, $solution = null)
    {
        parent::__construct($message, $solution, 'AssetException');
    }

}