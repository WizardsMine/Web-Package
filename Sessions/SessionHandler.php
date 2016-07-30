<?php

namespace Wizard\Sessions;

use Wizard\App\Session;
use Wizard\Kernel\App;
use Wizard\Modules\Config\Config;
use Wizard\Modules\Database\Model;
use Wizard\Modules\Database\ModelException;

class SessionHandler
{
    /**
     * @var array
     *
     * Available session driver types.
     */
    private $types = array('mysql', 'file');

    /**
     * @var null|string
     *
     * The session driver that will be used during the request.
     */
    private $driver = null;

    /**
     * @var null|Model
     *
     * If the session driver is mysql then this will be the query builder.
     */
    private $model = null;

    /**
     * @var string
     * The session id from the user.
     */
    private $key;

    /**
     * @throws SessionException
     *
     * Checks the config, removes old data and checks if the user has a valid session key
     */
    public function setup()
    {
        $this->loadConfig();
        if ($this->driver === null) {
            throw new SessionException('Could not find session driver');
        }
        $this->garbageCollection();
        if ($this->hasId() === false) {
            $key = $this->setId();
        } else {
            $key = $this->getCookie();
            $this->updateTime($key);
            $this->key = $key;
        }
        $this->loadData($key);
    }

    /**
     * @param string $session_id
     * @throws ModelException
     *
     * Updates data back to the database.
     */
    public function updateData(string $session_id)
    {
        switch ($this->driver) {
            case 'file':
                break;

            case 'mysql':
                $data = serialize(array('data' => Session::$data, 'flash' => Session::$next_flash));
                $this->model->update(['data' => $data])->where(['session_id' => $session_id])->execute();
                break;
        }
    }

    /**
     * @return string
     *
     * Returns the session id from the user.
     */
    public function getId()
    {
        return $this->key;
    }

    /**
     * @param $session_id
     * @throws SessionException
     * @throws ModelException
     *
     * Load the session data to the Session::$data property and the flash data to the Session::$flash property.
     */
    private function loadData(string $session_id)
    {
        switch ($this->driver) {
            case 'file':
                break;

            case 'mysql':
                $result = $this->model->select(['data'])->where(['session_id' => $session_id])->fetch();
                if ($result === false) {
                    throw new SessionException('Could not load session data');
                }
                $result = unserialize($result['data']) ?: array('data' => array(), 'flash' => array());
                Session::$data = $result['data'] ?? array();
                Session::$flash = $result['flash'] ?? array();
                break;
        }
    }

    /**
     * @return mixed
     *
     * Get the session cookie id.
     */
    private function getCookie()
    {
        return $_COOKIE['PHPWIZARDSESSION'];
    }

    /**
     * @param string $session_id
     * @throws ModelException
     *
     * Updates the expire time of a session key by added one hour to the current time.
     */
    private function updateTime(string $session_id)
    {
        switch ($this->driver) {
            case 'file':
                break;

            case 'mysql':
                $this->model->update(['time' => time() + 3600])->where(['session_id' => $session_id])->execute();
                break;
        }
    }

    /**
     * @return string
     * @throws SessionException
     * @throws ModelException
     *
     * Sets a new session cookie and returns the key that is set.
     */
    private function setId()
    {
        switch ($this->driver) {
            case 'file':
                break;

            case 'mysql':
                $key = $this->generateId();
                $success = $this->model->insert(['session_id' => $key, 'time' => time() + 3600])->execute()->rowCount();
                if ($success != 1) {
                    throw new SessionException('Could not store session_id in database');
                }
                setcookie('PHPWIZARDSESSION', $key);
                return $key;
                break;
        }
        throw new SessionException('Could not store session');
    }

    /**
     * @return bool
     * @throws ModelException
     *
     * Checks if there is a cookie with a session id.
     * If there is a cookie it also check if it is expired.
     */
    private function hasId()
    {
        if (array_key_exists('PHPWIZARDSESSION', $_COOKIE)) {
            switch ($this->driver) {
                case 'file':
                    return false;
                    break;

                case 'mysql':
                    $count = $this->model->select(['count(*)'])->where(['session_id' => $_COOKIE['PHPWIZARDSESSION'], ['time' , '>', time()]])->fetch()[0];
                    if ($count == 1) {
                        return true;
                    }
                    break;
            }
        }
        return false;
    }

    /**
     * @return string
     *
     * Generates a random string of 32 characters as session id.
     * Also checks if the string already exists and if not it generates a new one.
     */
    private function generateId()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 32; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        switch ($this->driver) {
            case 'file':
                break;

            case 'mysql':
                $count = $this->model->select(['count(*)'])->where(['session_id' => $randomString])->fetch()[0];
                if ($count != 0) {
                    return $this->generateId();
                }
                break;
        }
        return $randomString;
    }

    /**
     * @throws ModelException
     *
     * Deletes old session data with an expire time lower than the current time.
     */
    public function garbageCollection()
    {
        switch ($this->driver) {
            case 'file':
                break;

            case 'mysql':
                $this->model->delete()->where([['time', '<', time()]])->execute();
                break;
        }
    }

    /**
     * @throws SessionException
     *
     * Checks the session config and sets the database connection if the driver is mysql.
     * Also if the driver is file it checks if the path exists and throws errors if
     * something went wrong.
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
        if (!in_array($config['driver'], $this->types)) {
            throw new SessionException('Session driver is invalid');
        }
        $this->driver = $config['driver'];
        switch ($this->driver) {
            case 'file':
                if (!array_key_exists('file', $config) || !is_array($config['file'])) {
                    throw new SessionException('Session file config not found or value is not an array');
                }
                if (!array_key_exists('save_path', $config['file'])) {
                    throw new SessionException('Session file save path key not found');
                }
                if (!is_string($config['file']['save_path'])) {
                    throw new SessionException('Session save path must be a string');
                }
                if (!file_exists($config['file']['save_path'])) {
                    throw new SessionException('Session file save path doesnt exists');
                }
                break;

            case 'mysql':
                if (!array_key_exists('mysql', $config) || !is_array($config['mysql'])) {
                    throw new SessionException('Session mysql config not found or value is not an array');
                }
                if (!array_key_exists('table', $config['mysql']) || !is_string($config['mysql']['table'])) {
                    throw new SessionException('Session database table not found in config');
                }
                if (array_key_exists('db_config', $config['mysql'])) {
                    if (!is_bool($config['mysql']['db_config'])) {
                        throw new SessionException('Session use database connection key found but value is not a boolean');
                    }
                    if ($config['mysql']['db_config'] === true) {
                        if (App::$db_connection === null) {
                            throw new SessionException('Session using database connection but that connection is not found');
                        }
                        $model = new Model();
                        $model->setTable($config['mysql']['table']);
                        $model->connection = App::$db_connection;
                        $this->model = $model;
                        return;
                    }
                }
                if (!array_key_exists('host', $config['mysql']) || empty($config['mysql']['host']) || !is_string($config['mysql']['host'])) {
                    throw new SessionException('Session database host not found');
                }
                if (!array_key_exists('database', $config['mysql']) || empty($config['mysql']['database']) || !is_string($config['mysql']['database'])) {
                    throw new SessionException('Session database not found');
                }
                if (!array_key_exists('user', $config['mysql']) || empty($config['mysql']['user']) || !is_string($config['mysql']['user'])) {
                    throw new SessionException('Session database user not found');
                }
                if (!array_key_exists('password', $config['mysql']) || !is_string($config['mysql']['password'])) {
                    throw new SessionException('Session database password not found');
                }
                $model = new Model();
                $model->setTable($config['mysql']['table']);
                $model->connection = $model->connect($config['mysql']['database'], $config['mysql']['host'], $config['mysql']['user'], $config['mysql']['password']);
                $this->model = $model;
                break;
        }
    }
    
}