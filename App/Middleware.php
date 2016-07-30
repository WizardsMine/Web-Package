<?php

namespace Wizard\App;

use Wizard\Kernel\App;
use Wizard\Modules\AbstractModule;

class Middleware extends AbstractModule
{

    function __construct()
    {
        parent::__construct();
        $this->root = App::$root;
    }
}