<?php
namespace Roster\Database;

use PDO;
use PDOStatement;

class Grammer
{
    /**
     * Compile select
     *
     * @param QueryBuilder $query
     * @return string
     */
    public function compileSelect(QueryBuilder $query)
    {
        return "select ". implode(', ', $query->select) .
               " from $query->table ". implode(' ', $query->condition);
    }

    /**
     * Compile select
     *
     * @param $table
     * @param $condition
     * @return string
     */
    public function compileDelete($table, $condition)
    {
        return "delete from $table ". implode(' ', $condition);
    }

    /**
     * Compile update
     *
     * @param QueryBuilder $query
     * @param $columns
     * @return string
     */
    public function compileUpdate(QueryBuilder $query, $columns)
    {
        return "update $query->table set ". implode(', ', array_map(
            function ($column) {
                return "$column = ? ";
            }, array_keys($columns), $columns
        )). implode(' ', $query->condition);
    }

    /**F
     * @param $table
     * @param $columns
     * @return string
     */
    public function compileInsert($table, $columns)
    {
        return "insert into $table (". implode(', ', array_keys($columns)). ") values (" .
            implode(", ", array_map(function (){
                return '?';
            }, $columns)). ")";
    }

    /**
     * Bind value
     *
     * @param PDOStatement $statement
     * @param array $values
     */
    public function bindValues(PDOStatement $statement, array $values)
    {
        foreach ($values as $key => $value)
        {
            if (is_int($key))
            {
                $key++;
            }

            $statement->bindValue($key, $value, $this->parseParam($value));
        }
    }

    /**
     * Parse value form sql
     *
     * @param $value
     * @return int|string
     */
    public function parseParam($value)
    {
        if (is_string($value))
        {
            return PDO::PARAM_STR;
        }
        elseif (is_numeric($value))
        {
            return PDO::PARAM_INT;
        }
        elseif (is_bool($value))
        {
            return PDO::PARAM_BOOL;
        }
        elseif (is_null($value))
        {
            return PDO::PARAM_NULL;
        }
    }

}
