<?php

namespace Wizard\Src\Http\App;

use Wizard\Src\Modules\AbstractModule;

class Controller extends AbstractModule
{

    function __construct($root)
    {
        parent::__construct();
        $this->root = $root;
    }
}