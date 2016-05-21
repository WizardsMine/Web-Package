<?php

namespace Wizard\Modules\Database;

use Wizard\Exception\WizardRuntimeException;
use Wizard\Modules\Database\Statements\BaseStatements;

class Model extends Database
{
    use BaseStatements;

    public $table;
    
    public $DB_Connection = null;

    function __construct()
    {
        try {
            $this->setTable();
        } catch (\PDOException $e) {
            WizardRuntimeException::showStaticErrorPage($e);
        }
    }
    
    public function setTable(string $table = null)
    {
        if (empty($table)) {
            if (!isset($this->table)) {
                $class = get_class($this);
                $exploded = explode('\\', $class);
                $this->table = end($exploded);
            }
            if (get_class($this) == 'Wizard\Src\Modules\Database\Model') {
                $this->table = null;
            }
        } else {
            $this->table = $table;
        }
    }
}