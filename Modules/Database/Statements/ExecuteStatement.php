<?php

namespace Wizard\Modules\Database\Statements;

trait ExecuteStatement
{
    use CompileAndExecute;

    public function execute(array $parameters = array())
    {
        $backtrace = debug_backtrace()[0]['object'];

        $compiled = $this->compile($backtrace);
        $parameters = $backtrace->parameters ?? $parameters ?? array();
//        print_r($parameters);
//        echo '<br>';
        $executed = $this->executeStatement($backtrace->connection, $compiled, $parameters);

        return $executed;
    }
}