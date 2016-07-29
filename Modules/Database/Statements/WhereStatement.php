<?php

namespace Wizard\Modules\Database\Statements;

use Wizard\Modules\Database\ModelException;

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
                if ($value[1] == 'IN' && is_array($value[2])) {
                    $inloop = 0;
                    $invalue = '';
                    foreach ($value[2] as $item) {
                        $bindParams[] = $item;
                        if ($inloop == 0) {
                            $invalue .= '?';
                        } else {
                            $invalue .= ', ?';
                        }
                        $inloop++;
                    }
                    $is_in = true;
                } else {
                    $is_in = false;
                }
                if (count(explode('.', $value[0])) === 1) {
                    //Add table to column
                    if ($loop === 0) {
                        if ($is_in == true) {
                            $where .= $table . '.' . $value[0] . ' IN ('. $invalue .')';
                        } else {
                            $where .= $table . '.' . $value[0] . ' ' . $value[1] . ' ?';
                            $bindParams[] = $value[2];
                        }
                    } else {
                        if ($is_in == true) {
                            $where .= ' AND ' . $table . '.' . $value[0] . ' IN ('. $invalue. ')';
                        } else {
                            $where .= ' AND ' . $table . '.' . $value[0] . ' ' . $value[1] . ' ?';
                            $bindParams[] = $value[2];
                        }
                    }
                } else {
                    if ($loop === 0) {
                        if ($is_in == true) {
                            $where .= $value[0] . ' IN ('. $invalue .')';
                        } else {
                            $where .= $value[0] . ' ' . $value[1] . ' ?';
                            $bindParams[] = $value[2];
                        }
                    } else {
                        if ($is_in == true) {
                            $where .= ' AND ' . $value[0] . ' IN ('. $invalue .')';
                        } else {
                            $where .= ' AND ' . $value[0] . ' ' . $value[1] . ' ?';
                            $bindParams[] = $value[2];
                        }
                    }
                }
            } elseif (is_string($parameter) && (is_string($value) || is_int($value) || is_null($value))) {
//            } elseif (is_string($parameters) && is_string($value)) {
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
            use SubWhereStatements, FetchAndExecute, OrderBy;
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











