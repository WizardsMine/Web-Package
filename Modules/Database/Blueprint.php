<?php

namespace Wizard\Modules\Database;

class Blueprint
{

    public function uid()
    {
        //Auto generates a unique ID, int(11), primary key, auto increment

        return 'ID int NOT NULL AUTO_INCREMENT, PRIMARY KEY (ID)';
    }

    public function varchar()
    {
        
    }

    public function int(string $column)
    {
        return new class {

        };
    }

    public function text()
    {
        
    }

    public function enum()
    {
        
    }

    public function bool()
    {
        return '';
    }

    public function float()
    {
        
    }

    public function date()
    {
        
    }

    public function datetime()
    {
        
    }

    public function timestamp()
    {
        
    }

    public function time()
    {
        
    }
}