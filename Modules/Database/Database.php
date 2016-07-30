<?php

namespace Wizard\Modules\Database;

use Wizard\Exception\WizardRuntimeException;

class Database
{
    /**
     * @var null|\PDO
     * Holds the database connection for modals.
     */
    public $connection = null;

    /**
     * @param string $database
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $driver
     * @return \PDO
     * @throws DatabaseException
     * 
     * Connects to a database and return the PDO instance. If invalid driver, database, host or user is given
     * it will check the global database config and connect to 
     */
    public function connect(string $database, string $host, string $user, string $password, string $driver = 'mysql')
    {
        $dsn = $driver.':dbname='.$database.';host='.$host;
        try {
            $connection = new \PDO($dsn, $user, $password);
            return $connection;
        } catch (\PDOException $e) {
            WizardRuntimeException::showStaticErrorPage($e);
        }
    }
}