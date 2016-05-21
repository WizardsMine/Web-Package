<?php

namespace Wizard\Http\Exception;

use Wizard\Exception\WizardExceptionInterface;

interface RouteExceptionInterface extends WizardExceptionInterface
{

    public function showErrorPage();


}