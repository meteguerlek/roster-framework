<?php
namespace Roster\Container;

class Container
{
    protected static $container = [];

    public static function set($key, $value)
    {
        static::$container[$key] = $value;
    }

    public static function get($key)
    {
        return static::$container[$key];
    }
}
