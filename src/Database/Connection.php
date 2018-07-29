<?php

namespace Roster\Database;

use PDO;
use PDOException;

class Connection
{
    /**
     * @var object
     */
    private $connection;

    /**
     * @var null
     */
    private static $instance = null;

    /**
     * Connection constructor.
     */
    private function __construct()
    {
        $this->setConnection();
    }

    /**
     * Get connection
     *
     * @return $this
     */
    private function setConnection()
    {
        $host = config('config.connection.db_host');
        $user = config('config.connection.db_user');
        $password = config('config.connection.db_password');
        $name = config('config.connection.db_name');

        $options = [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ];

        try
        {
            $this->connection = new PDO('mysql:host='. $host .';dbname='. $name, $user, $password, $options);
        }
        catch (PDOException $e)
        {
            print_r($e->getMessage());
            print_r($e->getCode());
        }

        return $this;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get instance form connection
     *
     * @return bool
     */
    public static function getInstance() {

        if(static::$instance == null)
        {
            static::$instance = new static;
        }

        return static::$instance;
    }

}
