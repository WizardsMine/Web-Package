<?php

namespace Wizard\Src\Modules\Database\Statements;

trait SubSelectStatements
{
    public function get(array $columns)
    {
        $backtrace = debug_backtrace()[0]['object'];
        $newColumns = $backtrace->columns;
        $joinTable = $backtrace->joinTable;
        foreach ($columns as $column) {
            if (is_string($column)) {
                if (count(explode('.', $column)) === 1) {
                    if (empty($newColumns)) {
                        $newColumns .= $joinTable.'.'.$column;
                    } else {
                        $newColumns .= ','.$joinTable.'.'.$column;
                    }
                } else {
                    if (empty($newColumns)) {
                        $newColumns .= $column;
                    } else {
                        $newColumns .= ','.$column;
                    }
                }
            }
        }

        $Class = new class {
            use WhereStatement, OrderBy, FetchAndExecute;
        };
        $Class->connection = $backtrace->connection;
        $Class->table = $backtrace->table;
        $Class->type = $backtrace->type;
        $Class->hasJoin = $backtrace->hasJoin;
        $Class->joinType = $backtrace->joinType;
        $Class->joinValue = $backtrace->joinValue;
        $Class->joinTable = $backtrace->joinTable;
        $Class->columns = $newColumns;
        return $Class;
    }
}