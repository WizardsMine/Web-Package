<?php

namespace Wizard\App;

use Wizard\Kernel\App;
use Wizard\Modules\AbstractModule;

class Controller extends AbstractModule
{
    public $baseUri;

    function __construct()
    {
        parent::__construct();
        $this->root = App::$root;
        $this->baseUri = App::$base_uri;
    }

    public function request(string $uri)
    {
        return $this->baseUri.$uri;
    }

}