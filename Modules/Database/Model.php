<?php

namespace Wizard\Modules\Database;

use Wizard\Modules\Database\Statements\BaseStatements;

class Model extends Database
{
    use BaseStatements;

    /**
     * @var string
     * The table default table that will be used in the query builder.
     */
    public $table;

    function __construct()
    {
        $this->setTable();
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