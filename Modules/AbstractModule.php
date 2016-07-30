<?php

namespace Wizard\Modules;

use Wizard\App\Session;
use Wizard\Modules\Config\Config;
use Wizard\Modules\Database\Model;
use Wizard\Modules\Filesystem\Filesystem;
use Wizard\Modules\Mail\Mail;

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

        $this->model = new Model();

        $this->session = new Session();
    }

}