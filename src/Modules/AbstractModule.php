<?php

namespace Wizard\Src\Modules;

use Wizard\Src\Modules\Config\Config;
use Wizard\Src\Modules\Database\Model;
use Wizard\Src\Modules\Debugger\Debugger;
use Wizard\Src\Modules\Filesystem\Filesystem;
use Wizard\Src\Modules\Mail\Mail;
use Wizard\Src\Modules\Sessions\Session;

abstract class AbstractModule
{

    public $root;

    public $config;
    public $debugger;
    public $filesystem;
    public $mail;
    public $session;
    public $model;


    function __construct() {

        $this->config = new Config();

        $this->debugger = new Debugger();

        $this->filesystem = new Filesystem();

        $this->mail = new Mail();

        $this->session = new Session();

        $this->model = new Model();
    }

}