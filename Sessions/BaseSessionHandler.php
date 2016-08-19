<?php

namespace Wizard\Sessions;

use Wizard\App\Session;
use Wizard\Kernel\App;

abstract class BaseSessionHandler
{

    /**
     * @var string
     *
     * The cookie name that will be stored in the user's browser
     */
    public $cookie = 'PHPWIZARDSESSION';

    public $session_id = '';

    /**
     * BaseSessionHandler constructor.
     * @param array $config
     *
     * The construct method that handles the session.
     */
    public function __construct(array $config)
    {
        $this->garbageCollection();
        if (!$this->hasId()) {
            do {
                $key = $this->generateId();
            } while ($this->keyExists($key));
            $this->setId($key);
            setcookie($this->cookie, $key, 0, App::$base_uri.'/', null, null, true);
        } else {
            $key = $this->getCookie();
            $this->updateTime($key);
        }
        $this->session_id = $key;
        $data = unserialize($this->getData($key));
        Session::$flash = $data['flash'] ?? array();
        Session::$data = $data['data'] ?? array();
    }

    /**
     * @return mixed
     *
     * Get the cookie that is stored in the browser
     */
    protected function getCookie()
    {
        return $_COOKIE[$this->cookie];
    }

    public function getId()
    {
        return $this->session_id;
    }

    /**
     * @return string
     *
     * Generates a random string that can be used as cookie id.
     */
    public function generateId()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 32; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @param string $key
     * @return bool
     */
    abstract function keyExists(string $key);

    /**
     * @param string $key
     * @return array
     */
    abstract function getData(string $key);

    abstract function updateTime(string $key);

    abstract function updateData(string $key);

    /**
     * @return bool
     */
    abstract function hasId();

    abstract function setId(string $key);

    abstract function garbageCollection();
}