<?php

namespace Wizard\Modules;

use Wizard\Modules\Config\Config;
use Wizard\Modules\Database\Model;
use Wizard\Modules\Debugger\Debugger;
use Wizard\Modules\Filesystem\Filesystem;
use Wizard\Modules\Mail\Mail;
use Wizard\Modules\Sessions\Session;

abstract class AbstractModule
{

    public $root;

    public $config;
    public $filesystem;
    public $mail;
    public $session;
    public $model;


    function __construct() {

        $this->config = new Config();

        $this->filesystem = new Filesystem();

        $this->mail = new Mail();

        $this->session = new Session();

        $this->model = new Model();
    }

}