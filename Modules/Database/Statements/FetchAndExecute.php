<?php

namespace Wizard\Modules\Database\Statements;

trait FetchAndExecute
{
    use Fetching, ExecuteStatement {
        Fetching::compile insteadof ExecuteStatement;
        Fetching::selectType insteadof ExecuteStatement;
        Fetching::executeStatement insteadof ExecuteStatement;
        Fetching::rawType insteadof ExecuteStatement;
        Fetching::updateType insteadof ExecuteStatement;
        Fetching::insertType insteadof ExecuteStatement;
        Fetching::deleteType insteadof ExecuteStatement;
    }
}