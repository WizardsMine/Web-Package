<?php

namespace Wizard\Src\Modules\Database\Statements;

use Wizard\Src\Modules\Exception\ModelException;

trait WhereStatement
{
    public function where(array $parameters)
    {
        $backtrace = debug_backtrace()[0]['object'];
        if (empty($parameters)) {
            throw new ModelException('Empty array passed to where method');
        }
        $table = $backtrace->table;
        $bindParams = $backtrace->parameters ?? array();
        $loop = 0;
        $where = '';
        foreach ($parameters as $parameter => $value) {
            if (is_array($value)) {
                if (count($value) !== 3) {
                    throw new ModelException('An array in where clause as parameter must contain 3 values');
                }
                if (count(explode('.', $value[0])) === 1) {
                    //Add table to column
                    if ($loop === 0) {
                        $where .= $table.'.'.$value[0].' '.$value[1].' ?';
                        $bindParams[] = $value[2];
                    } else {
                        $where .= ' AND '.$table.'.'.$value[0].' '.$value[1].' ?';
                        $bindParams[] = $value[2];
                    }
                } else {
                    if ($loop === 0) {
                        $where .= $value[0].' '.$value[1].' ?';
                        $bindParams[] = $value[2];
                    } else {
                        $where .= ' AND '.$value[0].' '.$value[1].' ?';
                        $bindParams[] = $value[2];
                    }
                }
            } elseif (is_string($parameter) && is_string($value)) {
                if (count(explode('.', $parameter)) === 1) {
                    if ($loop === 0) {
                        $where .= $table.'.'.$parameter.'=?';
                        $bindParams[] = $value;
                    } else {
                        $where .= ' AND '.$table.'.'.$parameter.'=?';
                        $bindParams[] = $value;
                    }
                } else {
                    if ($loop === 0) {
                        $where .= $parameter.'=?';
                        $bindParams[] = $value;
                    } else {
                        $where .= ' AND '.$parameter.'=?';
                        $bindParams[] = $value;
                    }
                }
            } else {
                throw new ModelException('Invalid parameter values given in where method');
            }
            $loop++;
        }

        $Class = new class {
            use SubWhereStatements, FetchAndExecute;
        };
        $Class->connection = $backtrace->connection;
        $Class->type = $backtrace->type;
        $Class->table = $backtrace->table;
        $Class->where = $where;
        $Class->parameters = $bindParams;
        if (property_exists($backtrace, 'hasJoin') && $backtrace->hasJoin === true) {
            $Class->hasJoin = $backtrace->hasJoin;
            $Class->joinType = $backtrace->joinType;
            $Class->joinValue = $backtrace->joinValue;
            $Class->joinTable = $backtrace->joinTable;
        }
        switch ($backtrace->type) {
            case 'SELECT':
                $Class->columns = $backtrace->columns;
                break;

            case 'UPDATE':
                $Class->update = $backtrace->update;
                break;
        }
        return $Class;
    }
}











