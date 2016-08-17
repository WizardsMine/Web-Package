<?php

namespace Wizard\Sessions;

use Wizard\Modules\Config\Config;

class SessionHandler
{

    /**
     * @throws SessionException
     * @return BaseSessionHandler
     *
     * Checks the config, removes old data and checks if the user has a valid session key
     */
    public function setup()
    {
        $config = $this->loadConfig();
        return $this->initHandler($config);
    }

    /**
     * @param array $driver_config
     * @throws SessionException
     * @return BaseSessionHandler
     *
     * Checks if there is a handler key in the session config that is used and if that class exists.
     * Also checks if that class extends BaseSessionHandler class.
     */
    public function initHandler(array $driver_config)
    {
        if (!array_key_exists('handler', $driver_config)) {
            throw new SessionException('Couldnt find session handler');
        }
        if (!class_exists($driver_config['handler'])) {
            throw new SessionException('Session handler class not found');
        }
        $session_handler = new $driver_config['handler']($driver_config);
        if (!$session_handler instanceof BaseSessionHandler) {
            throw new SessionException('Session handler doesnt extend the BaseSessionHandler class');
        }
        return $session_handler;
    }

    /**
     * @throws SessionException
     * @return array
     *
     * Loads the config file and checks if there is a driver and if that driver exists.
     * After that it returns the file.
     */
    private function loadConfig()
    {
        $config = Config::getFile('session');
        if ($config === null) {
            throw new SessionException('Session config file not found');
        }
        if (!array_key_exists('driver', $config)) {
            throw new SessionException('Session config driver key not found');
        }
        if (!array_key_exists($config['driver'], $config)) {
            throw new SessionException('Session driver not found');
        }
        return $config[$config['driver']];

    }

}