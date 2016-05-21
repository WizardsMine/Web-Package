<?php

namespace Wizard\Modules\Database\Statements;

use Wizard\Modules\Exception\ModelException;

trait OrderBy
{

    public function orderBy(array $columns)
    {
        $backtrace = debug_backtrace()[0]['object'];
        if (empty($columns)) {
            throw new ModelException('Passed an empty array to orderBy method');
        }
        $orderBy = '';
        $loop = 0;
        foreach ($columns as $column => $type) {
            if (is_int($column)) {
                if ($loop === 0) {
                    $orderBy .= $type.' ASC';
                } else {
                    $orderBy .= ','.$type. 'ASC';
                }
            } elseif (is_string($column) && is_string($type)) {
                if ($loop === 0) {
                    $orderBy .= $column.' '.$type;
                } else {
                    $orderBy .= ','.$column.' '.$type;
                }
            } else {
                throw new ModelException('Invalid order by syntax');
            }
            $loop++;
        }
        $Class = $this->combinePreviousClass($backtrace);
        $Class->orderBy = $orderBy;
        return $Class;
    }

    private function combinePreviousClass($backtrace)
    {
        $Class = new class {
            use FetchAndExecute;
        };
        $Class->connection = $backtrace->connection;
        $Class->type = $backtrace->type;
        $Class->table = $backtrace->table;
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