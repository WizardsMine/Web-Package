<?php

namespace Wizard\Src\App;

use Wizard\Src\Modules\AbstractModule;

class Middleware extends AbstractModule
{

    function __construct($root)
    {
        parent::__construct();
        $this->root = $root;
    }
}