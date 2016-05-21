<?php

namespace Wizard\Modules\Database\Statements;

use Wizard\Modules\Exception\ModelException;

trait CompileAndExecute
{
    private function executeStatement(\PDO $pdo, string $statement, array $parameters = array())
    {
        //echo $statement.'<br>';
        $stmt = $pdo->prepare($statement);
        $stmt->execute($parameters);
        return $stmt;
    }

    private function compile($finalObject)
    {
        $statement = '';

        if (!property_exists($finalObject, 'type')) {
            throw new ModelException('No statement type found');
        }
        switch ($finalObject->type) {
            case 'SELECT':
                $statement = $this->selectType($finalObject);
                break;

            case 'INSERT':
                $statement = $this->insertType($finalObject);
                break;

            case 'UPDATE':
                $statement = $this->updateType($finalObject);
                break;

            case 'DELETE':
                $statement = $this->deleteType($finalObject);
                break;

            case 'RAW':
                $statement = $this->rawType($finalObject);
                break;
            default:
                echo 'none';
                break;
        }
        return $statement;
    }

    private function selectType($finalObject)
    {
        if (!property_exists($finalObject, 'columns')) {
            throw new ModelException('Select statement doesnt have a columns property');
        }
        if (!property_exists($finalObject, 'table')) {
            throw new ModelException('No table specified');
        }
        $statement = 'SELECT '.$finalObject->columns.' FROM '.$finalObject->table;

        if (property_exists($finalObject, 'hasJoin') && $finalObject->hasJoin === true) {
            if (!property_exists($finalObject, 'joinValue')) {
                throw new ModelException('It has a join but no value');
            }
            $statement .= ' '.$finalObject->joinValue;
        }
        if (property_exists($finalObject, 'where')) {
            $statement .= ' WHERE '.$finalObject->where;
        }
        if (property_exists($finalObject, 'orderBy')) {
            $statement .= ' ORDER BY '.$finalObject->orderBy;
        }

        return $statement;
    }

    private function rawType($finalObject)
    {
        if (!property_exists($finalObject, 'statement')) {
            throw new ModelException('Raw type without a statement property');
        }
        return $finalObject->statement;
    }

    private function updateType($finalObject)
    {
        if (!property_exists($finalObject, 'update')) {
            throw new ModelException('Update type without a update property');
        }
        if (!property_exists($finalObject, 'table')) {
            throw new ModelException('No table found in update statement');
        }
        $update = $finalObject->update;
        $table = $finalObject->table;
        if (property_exists($finalObject, 'hasJoin') && $finalObject->hasJoin === true) {
            $join = $finalObject->joinValue;
            $statement = "UPDATE $table $join SET $update";
        } else {
            $statement = "UPDATE $table SET $update";
        }
        if (property_exists($finalObject, 'where')) {
            $statement .= ' WHERE '.$finalObject->where;
        }
        if (property_exists($finalObject, 'orderBy')) {
            $statement .= ' ORDER BY '.$finalObject->orderBy;
        }
        return $statement;
    }

    private function insertType($finalObject)
    {
        if (!property_exists($finalObject, 'table')) {
            throw new ModelException('No table found in update statement');
        }
        $table = $finalObject->table;
        $statement = 'INSERT INTO '.$table;
        if (!empty($finalObject->insertColumns)) {
            $statement .= ' ('.$finalObject->insertColumns.')';
        }
        $statement .= ' VALUES ('.$finalObject->insertValues.')';

        return $statement;
    }

    private function deleteType($finalObject)
    {
        if (!property_exists($finalObject, 'table')) {
            throw new ModelException('No table found in update statement');
        }
        $table = $finalObject->table;
        $statement = 'DELETE FROM '.$table;
        if (property_exists($finalObject, 'where')) {
            $statement .= ' WHERE '.$finalObject->where;
        }
        return $statement;
    }
}











