<?php

namespace Roster\Cookie;

class Cookie
{
    /**
     * Set new cookie
     *
     * @param array ...$arguments
     * @return bool
     */
    public static function set(...$arguments)
    {
        return setcookie(...$arguments);
    }

    /**
     * Get cookie
     *
     * @param $name
     * @return mixed
     */
    public static function get($name)
    {
        return $_COOKIE[$name];
    }

    /**
     * Check if cookie exist
     *
     * @param $name
     * @return bool
     */
    public static function has($name)
    {
        return isset($_COOKIE[$name])
            ? true
            : false;
    }

    /**
     * Delete cookie
     *
     * @param $name
     */
    public static function destroy($name)
    {
        unset($_COOKIE[$name]);
    }
}
