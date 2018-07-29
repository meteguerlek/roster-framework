<?php

namespace Roster\Database;

use Roster\Database\Table\Plural;

class Table
{
    use Plural;

    //TODO:
    /*protected $collect = [];

    protected $table;

    protected $standard = [
        'NULL', 'CURRENT_TIMESTAMP'
    ];

    protected $attributes = [
        'BINARY', 'UNSIGNED'
    ];

    protected $index = [
        'PRIMARY', 'UNIQUE', 'INDEX', 'FULLTEXT', 'SPATIAL'
    ];

    public static function create($table, $callback)
    {
        if (!$callback instanceof \Closure)
        {
            throw new \Exception("To create table, you need a callback function");

            return;
        }

        $static = new static;

        $static->table = $table;

        return call_user_func($callback, $static);
    }

    public function int($name, $length = 11)
    {
        $this->collect[] = "$name int($length)";
    }


    protected function compile()
    {
        $sql = "CREATE TABLE IF NOT EXISTS '$this->table'";
    }*/


    public static function create($sql)
    {
        $instance = Connection::getInstance();

        $connection = $instance->getConnection();

        $connection->exec($sql);
    }


}
