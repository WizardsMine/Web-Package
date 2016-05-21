<?php

namespace Wizard\Modules\Database\Statements;

trait Fetching
{
    use CompileAndExecute;

    public function fetch()
    {
        $backtrace = debug_backtrace()[0]['object'];

        $compiled = $this->compile($backtrace);
        $parameters = $backtrace->parameters ?? array();
//        print_r($parameters);
//        echo '<br>';
        $executed = $this->executeStatement($backtrace->connection, $compiled, $parameters);
        return $executed->fetch();
    }

    public function fetchAll()
    {
        $backtrace = debug_backtrace()[0]['object'];
        
        $compiled = $this->compile($backtrace);
        $parameters = $backtrace->parameters ?? array();
//        print_r($parameters);
//        echo '<br>';
        $executed = $this->executeStatement($backtrace->connection, $compiled, $parameters);
        return $executed->fetchAll();
    }
}