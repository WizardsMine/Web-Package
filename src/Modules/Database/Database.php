<?php

namespace Wizard\Src\Modules\Database;

use Wizard\Src\Modules\Config\Config;
use Wizard\Src\Modules\Exception\DatabaseException;

class Database
{
    static $DBConnection;

    public function connect(string $database = '', string $host = '', string $user = '', string $password = '', string $driver = 'mysql')
    {
        if (!empty($driver) && !empty($database) && !empty($host) && !empty($user) && !empty($password)) {
            goto connect;
        }
        $db_credentials = Config::getFile('database');
        if ($db_credentials === null) {
            throw new DatabaseException("Couldn't find database config file");
        }
        if (!is_array($db_credentials)) {
            throw new DatabaseException("Database config file didn't return an array");
        }
        if (!array_key_exists('driver', $db_credentials)) {
            throw new DatabaseException("Couldn't find database driver");
        }
        $driver = $db_credentials['driver'];
        if ($driver !== 'mysql') {
            throw new DatabaseException('Unknown database driver');
        }
        $driverConfig = $db_credentials[$driver];
        if (!array_key_exists('host', $driverConfig) || !array_key_exists('database', $driverConfig) || !array_key_exists('user', $driverConfig) || !array_key_exists('password', $driverConfig)) {
            throw new DatabaseException("Couldn't find all database credentials");
        }
        $database = $driverConfig['database'];
        $host = $driverConfig['host'];
        $user = $driverConfig['user'];
        $password = $driverConfig['password'];
        connect:
        $dsn = $driver.':dbname='.$database.';host='.$host;
        return new \PDO($dsn, $user, $password);
    }
}