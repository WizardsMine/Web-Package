<?php

namespace Wizard\Src\Modules\Database\Statements;

use Wizard\Src\Modules\Database\Database;
use Wizard\Src\Modules\Exception\DatabaseException;
use Wizard\Src\Modules\Exception\ModelException;

trait BaseStatements
{
    /**
     * @param array $columns
     * @param string $table
     * @return mixed
     * @throws ModelException
     *
     * Changes an array of string values (each value is an column)
     * into a single string acceptable for using in SQL queries.
     *
     * Returns an new class with available methods to extend the statement
     */
    public function select(array $columns, string $table = null)
    {
        if ($this->table === null && $table === null) {
            throw new ModelException('No table found');
        }
        $table = $table ?? $this->getTable();

        $columnParameter = $columns;
        $columns = '';

        if (count($columns) < 1) {
            throw new ModelException('No columns specified');
        }
        $columns = '';
        $loop = 0;
        foreach ($columnParameter as $column) {
            if ($loop === 0) {
                if (count(explode('.', $column)) > 1) {
                    $columns .= $column;
                } else {
                    $columns .= $table.'.'.$column;
                }
            } else {
                if (count(explode('.', $column)) > 1) {
                    $columns .= ','. $column;
                } else {
                    $columns .= ','. $table.'.'.$column;
                }
            }
            $loop++;
        }

        $Class = new class {
            use Joins, WhereStatement, OrderBy, FetchAndExecute;
        };
        $Class->connection = debug_backtrace()[0]['object']->DB_Connection ?? Database::$DBConnection;
        if ($Class->connection === null) {
            throw new ModelException('No database connection found');
        }
        $Class->table = $table;
        $Class->type = 'SELECT';
        $Class->columns = $columns;
        return $Class;
    }

    /**
     * @param array $column_and_values
     * @param string $table
     * @throws ModelException
     * @return mixed
     * 
     * Turns an array with keys and values into a single string
     * that can be used for SQL queries.
     * 
     * This method also accepts no parameter as that will be used for 
     * joins and after the join method you will be able to use the
     * set method in the SubUpdateStatements trait.
     */
    public function update(array $column_and_values = array(), string $table = null)
    {
        if ($this->table === null && $table === null) {
            throw new ModelException('No table found');
        }
        $table = $table ?? $this->getTable();
        $loop = 0;
        $update = '';
        $params = array();
        foreach ($column_and_values as $column => $value) {
            if (!is_string($column)) {
                throw new ModelException('Column in update statement must be an string');
            }
            if (!is_string($value)) {
                throw new ModelException('Value in update statement must be an string');
            }
            if ($loop === 0) {
                $update .= $table.'.'.$column.'=?';
                $params[] = $value;
            } else {
                $update .= ','.$table.'.'.$column.'=?';
                $params[] = $value;
            }
            $loop++;
        }
        $Class = new class {
            use Joins, WhereStatement, FetchAndExecute, SubUpdateStatements;
        };
        $Class->connection = debug_backtrace()[0]['object']->DB_Connection ?? Database::$DBConnection;
        if ($Class->connection === null) {
            throw new ModelException('No database connection found');
        }
        $Class->parameters = $params;
        $Class->table = $table;
        $Class->type = 'UPDATE';
        $Class->update = $update;
        return $Class;
    }

    public function insert(array $column_and_values, string $table = null)
    {
        if (empty($column_and_values)) {
            throw new ModelException('Cant pass empty array to insert method');
        }
        if ($this->table === null && $table === null) {
            throw new ModelException('No table found');
        }
        $table = $table ?? $this->getTable();
        $loop = 0;
        $insertColumns = '';
        $insertValues = '';
        $params = array();
        foreach ($column_and_values as $column => $value) {
            if (!is_int($column) && !is_string($column)) {
                throw new ModelException('Invalid column syntax in insert method');
            }
            if (is_int($column)) {
                if (!empty($insertColumns)) {
                    throw new ModelException('Parameters passed to insert method must all have an key or none must have a key');
                }
                unset($insertColumns);
                if (!is_string($value)) {
                    throw new ModelException('Insert value must be a string');
                }
                if ($loop === 0) {
                    $insertValues .= '?';
                    $params[] = $value;
                } else {
                    $insertValues .= ',?';
                    $params[] = $value;
                }
            } else {
                if (!isset($insertColumns)) {
                    throw new ModelException('Parameters passed to insert method must all have an key or none must have a key');
                }
                if (!is_string($value)) {
                    throw new ModelException('Insert value must be a string');
                }
                if ($loop === 0) {
                    $insertColumns .= $column;
                    $insertValues .= '?';
                    $params[] = $value;
                } else {
                    $insertColumns .= ','.$column;
                    $insertValues .= ',?';
                    $params[] = $value;
                }
            }
            $loop++;
        }

        $Class = new class {
            use FetchAndExecute;
        };
        $Class->connection = debug_backtrace()[0]['object']->DB_Connection ?? Database::$DBConnection;
        if ($Class->connection === null) {
            throw new ModelException('No database connection found');
        }
        $Class->table = $table;
        $Class->type = 'INSERT';
        $Class->parameters = $params;
        $Class->insertColumns = $insertColumns ?? '';
        $Class->insertValues = $insertValues;
        return $Class;
    }

    public function delete(string $table = null)
    {
        if ($this->table === null && $table === null) {
            throw new ModelException('No table found');
        }
        $table = $table ?? $this->getTable();
        $Class = new class {
            use WhereStatement, FetchAndExecute;
        };
        $Class->connection = debug_backtrace()[0]['object']->DB_Connection ?? Database::$DBConnection;
        if ($Class->connection === null) {
            throw new ModelException('No database connection found');
        }
        $Class->table = $table;
        $Class->type = 'DELETE';
        return $Class;
    }

    public function raw(string $statement, array $placeholders = array())
    {
        $Class = new class {
            use FetchAndExecute;
        };
        $Class->connection = debug_backtrace()[0]['object']->DB_Connection ?? Database::$DBConnection;
        if ($Class->connection === null) {
            throw new ModelException('No database connection found');
        }
        $Class->type = 'RAW';
        $Class->statement = $statement;
        $Class->parameters = $placeholders;
        return $Class;
    }

    private function getTable()
    {
        return $table = debug_backtrace()[0]['object']->table;
    }
    
    private function checkDBConnection()
    {
        
        
        return true;
    }
}