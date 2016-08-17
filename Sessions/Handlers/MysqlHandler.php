<?php

namespace Wizard\Sessions\Handlers;

use Wizard\App\Session;
use Wizard\Kernel\App;
use Wizard\Modules\Database\Model;
use Wizard\Modules\Database\ModelException;
use Wizard\Sessions\BaseSessionHandler;
use Wizard\Sessions\SessionException;

class MysqlHandler extends BaseSessionHandler
{

    /**
     * @var Model
     * The database model that is used to get and put the session data.
     */
    private $model;

    /**
     * MysqlHandler constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->validateConfig($config);

        parent::__construct($config);
    }

    /**
     * @param string $key
     * @return array
     * @throws ModelException
     * @throws SessionException
     */
    public function getData(string $key)
    {
        $result = $this->model->select(['data'])->where(['session_id' => $key])->fetch();
        if ($result === false) {
            throw new SessionException('Could not load session data');
        }
        return $result['data'] ?: serialize(array('data' => array(), 'flash' => array()));
    }

    /**
     * @param string $key
     * @throws ModelException
     * @throws SessionException
     */
    public function setId(string $key)
    {
        $success = $this->model->insert(['session_id' => $key, 'time' => time() + 3600])->execute()->rowCount();
        if ($success != 1) {
            throw new SessionException('Could not store session_id in database');
        }
    }

    /**
     * @throws ModelException
     */
    public function garbageCollection()
    {
        $this->model->delete()->where([['time', '<', time()]])->execute();
    }

    /**
     * @param string $key
     * @throws ModelException
     */
    public function updateTime(string $key)
    {
        $this->model->update(['time' => time() + 3600])->where(['session_id' => $key])->execute();
    }

    /**
     * @return bool
     * @throws ModelException
     */
    public function hasId()
    {
        $count = $this->model->select(['count(*)'])->where(['session_id' => $_COOKIE['PHPWIZARDSESSION'], ['time' , '>', time()]])->fetch()[0];
        if ($count == 1) {
            return true;
        }
        return false;
    }

    /**
     * @param string $key
     * @return bool
     * @throws ModelException
     */
    public function keyExists(string $key)
    {
        $count = $this->model->select(['count(*)'])->where(['session_id' => $key])->fetch()[0];
        if ($count == 0) {
            return false;
        }
        return true;
    }

    /**
     * @param string $key
     * @throws ModelException
     */
    public function updateData(string $key)
    {
        $data = serialize(array('data' => Session::$data, 'flash' => Session::$next_flash));
        $this->model->update(['data' => $data])->where(['session_id' => $key])->execute();
    }

    /**
     * @param $config
     * @throws SessionException
     *
     * Validates the config and throws errors if there is something missing.
     */
    public function validateConfig($config)
    {
        if (!array_key_exists('table', $config) || !is_string($config['table'])) {
            throw new SessionException('Session database table not found in config');
        }
        if (array_key_exists('db_config', $config)) {
            if (!is_bool($config['db_config'])) {
                throw new SessionException('Session use database connection key found but value is not a boolean');
            }
            if ($config['db_config'] === true) {
                if (App::$db_connection === null) {
                    throw new SessionException('Session using database connection but that connection is not found');
                }
                $model = new Model();
                $model->setTable($config['table']);
                $model->connection = App::$db_connection;
                $this->model = $model;
                return;
            }
        }
        if (!array_key_exists('host', $config) || empty($config['host']) || !is_string($config['host'])) {
            throw new SessionException('Session database host not found');
        }
        if (!array_key_exists('database', $config) || empty($config['database']) || !is_string($config['database'])) {
            throw new SessionException('Session database not found');
        }
        if (!array_key_exists('user', $config) || empty($config['mysql']['user']) || !is_string($config['user'])) {
            throw new SessionException('Session database user not found');
        }
        if (!array_key_exists('password', $config) || !is_string($config['password'])) {
            throw new SessionException('Session database password not found');
        }
        $model = new Model();
        $model->setTable($config['table']);
        $model->connection = $model->connect($config['database'], $config['host'], $config['user'], $config['password']);
        $this->model = $model;
    }
}