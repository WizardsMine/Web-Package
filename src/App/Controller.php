<?php

namespace Wizard\Src\App;

use Wizard\Src\Kernel\App;
use Wizard\Src\Modules\AbstractModule;

class Controller extends AbstractModule
{
    public $baseUri;

    function __construct($root)
    {
        parent::__construct();
        $this->root = $root;
        $this->baseUri = App::$BaseUri;
    }

    public function request(string $uri)
    {
        return $this->baseUri.$uri;
    }

}