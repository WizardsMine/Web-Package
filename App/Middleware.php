<?php

namespace Wizard\App;

use Wizard\Modules\AbstractModule;

class Middleware extends AbstractModule
{

    function __construct($root)
    {
        parent::__construct();
        $this->root = $root;
    }
}