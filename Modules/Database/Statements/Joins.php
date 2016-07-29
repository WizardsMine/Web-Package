<?php

namespace Wizard\Modules\Database\Statements;

use Wizard\Modules\Database\ModelException;

trait Joins
{

    public function innerJoin(string $table, array $joinOn)
    {
        $backtraceObject = debug_backtrace()[0]['object'];

        $joinColumns = $this->validateJoin($joinOn);

        $join = "INNER JOIN $table ON $joinColumns";


        $Class = $this->returnType($backtraceObject);
        $Class->hasJoin = true;
        $Class->joinType = 'innerJoin';
        $Class->joinValue = $join;
        $Class->joinTable = $table;
        return $Class;
    }

    public function leftJoin(string $table, array $joinOn)
    {
        $backtraceObject = debug_backtrace()[0]['object'];

        $joinColumns = $this->validateJoin($joinOn);

        $join = "LEFT JOIN $table ON $joinColumns";


        $Class = $this->returnType($backtraceObject);
        $Class->hasJoin = true;
        $Class->joinType = 'innerJoin';
        $Class->joinValue = $join;
        $Class->joinTable = $table;
        return $Class;
    }

    public function rightJoin(string $table, array $joinOn)
    {
        $backtraceObject = debug_backtrace()[0]['object'];

        $joinColumns = $this->validateJoin($joinOn);

        $join = "RIGHT JOIN $table ON $joinColumns";


        $Class = $this->returnType($backtraceObject);
        $Class->hasJoin = true;
        $Class->joinType = 'innerJoin';
        $Class->joinValue = $join;
        $Class->joinTable = $table;
        return $Class;
    }

    public function fullJoin(string $table, array $joinOn)
    {
        $backtraceObject = debug_backtrace()[0]['object'];

        $joinColumns = $this->validateJoin($joinOn);

        $join = "FULL JOIN $table ON $joinColumns";


        $Class = $this->returnType($backtraceObject);
        $Class->hasJoin = true;
        $Class->joinType = 'innerJoin';
        $Class->joinValue = $join;
        $Class->joinTable = $table;
        return $Class;
    }

    private function validateJoin($join)
    {
        $joinOn = '';
        $loop = 0;
        foreach ($join as $key => $value) {
            if (is_array($value)) {
                if (!count($value) === 3) {
                    throw new ModelException('Join on array can only have 3 values');
                }
                if ($this->validateJoinColumn($value[0]) && $this->validateJoinColumn($value[2])) {
                    if ($loop == 0) {
                        $joinOn .= $value[0].$value[1].$value[2];
                    } else {
                        $joinOn .= ','.$value[0].$value[1].$value[2];
                    }
                    $loop++;
                }
            } elseif (is_string($key) && is_string($value)) {
                if ($this->validateJoinColumn($key) && $this->validateJoinColumn($value)) {
                    if ($loop == 0) {
                        $joinOn .= $key.'='.$value;
                    } else {
                        $joinOn .= ','.$key.'='.$value;
                    }
                    $loop++;
                }
            } else {
                throw new ModelException('No valid join on values specified');
            }
        }
        return $joinOn;
    }

    private function validateJoinColumn($column)
    {
        if (!is_string($column)) {
            throw new ModelException($column." isn't a string");
        }
        if (count(explode('.',$column)) > 2) {
            throw new ModelException('An column in a joining string cant have more then 1 dot');
        }

        if (count(explode('.',$column)) < 1) {
            throw new ModelException('An column in a joining string must have at least 1 dot');
        }
        return true;
    }

    private function returnType($backtrace)
    {
        switch ($backtrace->type) {
            case 'SELECT':
                $Class = new class {
                    use WhereStatement, OrderBy, SubSelectStatements, FetchAndExecute;
                };
                $Class->columns = $backtrace->columns;
                break;

            case 'UPDATE':
                $Class = new class {
                    use WhereStatement, SubUpdateStatements, FetchAndExecute;
                };
                $Class->update = $backtrace->update;
                break;

            case 'DELETE':
                $Class = new class {
                    use WhereStatement, FetchAndExecute;
                };
                break;
            default:
                throw new ModelException("Join type isn't a valid type, (select, update, delete)");
                break;
        }

        $Class->connection = $backtrace->connection;
        $Class->table = $backtrace->table;
        $Class->type = $backtrace->type;
        $Class->parameters = $backtrace->parameters ?? array();

        return $Class;
    }
}