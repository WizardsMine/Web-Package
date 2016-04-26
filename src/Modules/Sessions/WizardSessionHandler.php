<?php

namespace Wizard\Src\Modules\Sessions;

use Wizard\Src\Modules\Config\Config;
use Wizard\Src\Modules\Database\Database;
use Wizard\Src\Modules\Database\Model;
use Wizard\Src\Modules\Exception\ModelException;
use Wizard\Src\Modules\Exception\SessionException;

class WizardSessionHandler implements \SessionHandlerInterface
{
    /**
     * @var $savePath
     * Path for session files when file driver is used.
     */
    private $savePath;

    private $DBConnection;

    private $model;

    private $driver;

    /**
     * WizardSessionHandler constructor.
     * Checks the config and driver validation.
     */
    function __construct()
    {
        $this->driver = $this->getDriver();
        if ($this->driver == 'mysql') {
            $this->checkTableExist();
        }
    }

    /**
     * @param string $savePath
     * @param string $sessionName
     * @return bool Returns true on success
     *
     * This gets executed when session_start() is called.
     * Works like a constructor.
     */
    public function open($savePath, $sessionName)
    {
        switch ($this->driver) {
            case 'file':
                return true;
                break;
            case 'mysql':
                if ($this->DBConnection instanceof \PDO) {
                    return true;
                }
                return false;
                break;
            default:
                return false;
        }
    }

    /**
     * @return bool
     * The close callback works like a destructor in classes
     * and is executed after the session write callback has been called.
     * It is also invoked when session_write_close() is called.
     * Return value should be TRUE for success, FALSE for failure.
     */
    public function close()
    {
        $this->DBConnection = null;
        $this->model = null;
        return true;
    }

    /**
     * @param string $id
     * @return string
     * 
     * Called when the $_SESSION variable is used.
     */
    public function read($id)
    {
        switch ($this->driver) {
            case 'file':
                return (string)@file_get_contents("$this->savePath/sess_$id");
                break;
            case 'mysql':
                // Set query
                $data = $this->model->select(['data'])->where(['id' => $id])->fetch();
                return $data['data'];
                break;
        }
        return serialize('');
    }

    /**
     * @param string $id
     * @param string $data
     * @return bool
     *
     * This callback is invoked when PHP shuts down or explicitly when session_write_close() is called.
     *
     * Note:
     * The "write" handler is not executed until after the output stream is closed.
     */
    public function write($id, $data)
    {
        switch ($this->driver) {
            case 'file':
                return file_put_contents("$this->savePath/sess_$id", $data) === false ? false : true;
                break;
            case 'mysql':
                $access = time();
                $table = $this->model->table ?? 'sessions';
                if (empty($data)) {
                    goto goBack;
                }
                $this->model->raw("REPLACE INTO $table VALUES (:id, :access, :data)", [':id' => $id, ':access' => $access, ':data' => $data])->execute();
                goBack:
                return true;
                break;
            default:
                return false;
        }
    }

    /**
     * @param string $id
     * @return bool
     * Return value should be TRUE for success, FALSE for failure.
     *
     * This callback is executed when a session is destroyed with session_destroy() or with
     * session_regenerate_id() with the destroy parameter set to TRUE.
     */
    public function destroy($id)
    {
        switch ($this->driver) {
            case 'file':
                $file = "$this->savePath/sess_$id";
                if (file_exists($file)) {
                    unlink($file);
                }
                break;
            case 'mysql':
                $table = $this->model->table ?? 'sessions';
                $this->model->raw("DELETE FROM $table WHERE id = :id", [':id' => $id])->execute();
                return true;
        }
        return true;
    }

    /**
     * @param int $maxlifetime
     * @return bool
     *
     * The garbage collector callback is invoked internally by PHP periodically in order to purge old session data.
     */
    public function gc($maxlifetime)
    {
        switch ($this->driver) {
            case 'file':
                foreach (glob("$this->savePath/sess_*") as $file) {
                if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                    unlink($file);
                    }
                }
                break;
            case 'mysql':
                $old = time() - $maxlifetime;
                $this->model->raw("DELETE * FROM sessions WHERE access < :old", [':old' => $old])->execute();
                break;
        }
        return true;
    }


    public function getDriver()
    {
        $config = Config::getFile('session');
        if ($config === null) {
            throw new SessionException('Session config file not found');
        }
        if (!is_array($config)) {
            throw new SessionException('Session config file didnt return an array');
        }
        if (!array_key_exists('driver', $config) || !is_string($config['driver'])) {
            throw new SessionException('Session driver not found');
        }
        switch ($config['driver']) {
            case 'file':
                if (!array_key_exists('path', $config['file']) || !is_dir($config['file']['path'])) {
                    throw new SessionException('Session driver file path not found');
                }
                $this->savePath = $config['file']['path'];
                return 'file';
                break;

            case 'mysql':
                if (!array_key_exists('use_database_config', $config['mysql'])) {
                    throw new SessionException('Dont know which mysql config to use');
                }
                if ($config['mysql']['use_database_config'] === true) {
                    goto connected;
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
                if (!array_key_exists('table', $config['mysql']) || !is_string($config['mysql']['table'])) {
                    throw new SessionException('Session database table not found in config');
                }
                $this->model = new Model();
                if (!empty($config['mysql']['table'])) {
                    $this->model->setTable($config['mysql']['table']);
                }
                $this->DBConnection = $this->model->connect($config['mysql']['database'], $config['mysql']['host'], $config['mysql']['user'], $config['mysql']['password']);
                $this->model->DB_Connection = $this->DBConnection;
                return 'mysql';

                connected:
                if (Database::$DBConnection === null) {
                    throw new SessionException('No database connection found to load sessions');
                }
                $this->DBConnection = Database::$DBConnection;
                $this->model = new Model();
                $this->model->setTable('sessions');
                if (!$this->DBConnection instanceof \PDO) {
                    throw new SessionException('Static database connection not stance of PDO');
                }
                return 'mysql';
                break;

            default:
                throw new SessionException('Unknown session driver');
        }
    }

    public function checkTableExist()
    {
        $this->DBConnection;
        $table = $this->model->table ?? 'sessions';
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            `id` varchar(32) NOT NULL,
            `access` int(10) unsigned DEFAULT NULL,
            `data` text,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $this->model->raw($sql)->execute();
    }
}











