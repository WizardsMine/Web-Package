<?php

namespace Wizard\Modules\Database\Statements;

trait SubWhereStatements
{
    public function andLike(string $column, string $value)
    {
        return $this->like($column, $value, 'andLike', debug_backtrace()[0]['object']);
    }

    public function orLike(string $column, string $value)
    {
        return $this->like($column, $value, 'orLike', debug_backtrace()[0]['object']);
    }

    public function andNotLike(string $column, string $value)
    {
        return $this->like($column, $value, 'andNotLike', debug_backtrace()[0]['object']);
    }

    public function orNotLike(string $column, string $value)
    {
        return $this->like($column, $value, 'orNotLike', debug_backtrace()[0]['object']);
    }

    private function like(string $column, string $value, string $type, $backtrace)
    {
        $table = $backtrace->table;
        $where = $backtrace->where;
        switch ($type){
            case 'andLike':
                $where .= ' AND '.$this->validateColumn($column, $table).' LIKE ?';
                break;
            case 'andNotLike':
                $where .= ' AND '.$this->validateColumn($column, $table).' NOT LIKE ?';
                break;
            case 'orLike':
                $where .= ' OR '.$this->validateColumn($column, $table).' LIKE ?';
                break;
            case 'orNotLike':
                $where .= ' OR '.$this->validateColumn($column, $table).' NOT LIKE ?';
                break;
        }
        $where .= "'".$value."'";
        $Class = $this->previousClass($backtrace);
        $Class->paraeters = $backtrace->parameters[] = $value ?? array($value);
        $Class->where = $where;
        return $Class;
    }

    public function andBetween(string $column, string $first_value, string $second_value)
    {
        return $this->between($column, $first_value, $second_value, 'andBetween', debug_backtrace()[0]['object']);
    }

    public function orBetween(string $column, string $first_value, string $second_value)
    {
        return $this->between($column, $first_value, $second_value, 'orBetween', debug_backtrace()[0]['object']);
    }

    public function andNotBetween(string $column, string $first_value, string $second_value)
    {
        return $this->between($column, $first_value, $second_value, 'andNotBetween', debug_backtrace()[0]['object']);
    }

    public function orNotBetween(string $column, string $first_value, string $second_value)
    {
        return $this->between($column, $first_value, $second_value, 'orNotBetween', debug_backtrace()[0]['object']);
    }

    private function between(string $column, string $first_value, string $second_value, string $type, $backtrace)
    {
        $table = $backtrace->table;
        $where = '';
        switch ($type){
            case 'andBetween':
                $where .= ' AND '.$this->validateColumn($column, $table).' BETWEEN ';
                break;
            case 'andNotBetween':
                $where .= ' AND '.$this->validateColumn($column, $table).' NOT BETWEEN ';
                break;
            case 'orBetween':
                $where .= ' OR '.$this->validateColumn($column, $table).' BETWEEN ';
                break;
            case 'orNotBetween':
                $where .= ' OR '.$this->validateColumn($column, $table).' NOT BETWEEN ';
                break;
        }
        $where .= '? AND ?';
        $Class = $this->previousClass($backtrace);
        $Class->parameters = $backtrace->parameters[] = $first_value;
        $Class->parameters = $backtrace->parameters[] = $second_value;
        $Class->where = $where;
        return $Class;
    }

    private function validateColumn(string $column, string $table)
    {
        if (count(explode('.', $column)) === 1) {
            return $table.'.'.$column;
        }
        return $column;
    }

    public function in()
    {
        //TODO database in method
        $backtrace = debug_backtrace()[0]['object'];
        $Class = $this->previousClass($backtrace);
        return $Class;
    }

    public function and(string $column, string $operator, string $value)
    {
        $backtrace = debug_backtrace()[0]['object'];
        $table = $backtrace->table;
        $where = $backtrace->where.' AND '.$this->validateColumn($column, $table).$operator.'?';
        $Class = $this->previousClass($backtrace);
        $Class->parameters = $backtrace->parameters[] = $value ?? array($value);
        $Class->where = $where;
        return $Class;
    }

    public function or(string $column, string $operator, string $value)
    {
        $backtrace = debug_backtrace()[0]['object'];
        $table = $backtrace->table;
        $where = $backtrace->where.' OR '.$this->validateColumn($column, $table).$operator.'?';
        $Class = $this->previousClass($backtrace);
        $Class->parameters = $backtrace->parameters[] = $value ?? array($value);
        $Class->where = $where;
        return $Class;
    }

    private function previousClass($backtrace)
    {
        $Class = new class {
            use SubWhereStatements, FetchAndExecute, OrderBy;
        };
        $Class->connection = $backtrace->connection;
        $Class->type = $backtrace->type;
        $Class->table = $backtrace->table;
        $Class->where = $backtrace->where;
        $Class->parameters = $backtrace->parameters;
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