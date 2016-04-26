<?php

namespace Wizard\Src\Http\Exception;

use Wizard\Src\Exception\WizardExceptionInterface;

interface RouteExceptionInterface extends WizardExceptionInterface
{

    public function showErrorPage();


}