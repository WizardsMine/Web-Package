<?php

namespace Wizard\Src\Modules\Database\Statements;

use Wizard\Src\Modules\Exception\ModelException;

trait SubUpdateStatements
{

    public function set(array $column_and_values)
    {
        $backtrace = debug_backtrace()[0]['object'];
        $joinTable = $backtrace->joinTable;
        $update = $backtrace->update;
        foreach ($column_and_values as $column => $value) {
            if (!is_string($column)) {
                throw new ModelException('Column in update statement is not a string');
            }
            if (!is_string($value)) {
                throw new ModelException('Value in update statement is not a string');
            }
            if (count(explode('.', $column)) === 1) {
                if (empty($update)) {
                    $update .= $joinTable.'.'.$column.'='."'".$value."'";
                } else {
                    $update .= ','.$joinTable.'.'.$column.'='."'".$value."'";
                }
            } else {
                if (empty($update)) {
                    $update .= $column.'='."'".$value."'";
                } else {
                    $update .= ','.$column.'='."'".$value."'";
                }
            }
        }

        $Class = new class {
            use WhereStatement, FetchAndExecute;
        };
        $Class->connection = $backtrace->connection;
        $Class->table = $backtrace->table;
        $Class->type = $backtrace->type;
        $Class->hasJoin = $backtrace->hasJoin;
        $Class->joinType = $backtrace->joinType;
        $Class->joinValue = $backtrace->joinValue;
        $Class->joinTable = $backtrace->joinTable;
        $Class->update = $update;
        return $Class;
    }

}