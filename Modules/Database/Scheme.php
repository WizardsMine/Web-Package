<?php

namespace Wizard\Modules\Database;

class Scheme
{
    public static function table(string $table, array $columns)
    {
        $sql = 'CREATE TABLE '. $table .' (';
        foreach ($columns as $column) {
            
        }
    }

    public static function update(array $procedure)
    {

    }
}