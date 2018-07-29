<?php

namespace Roster\Environment;

class Env
{
    /**
     * Put environment
     *
     * @param $name
     * @param $contant
     * @return bool
     */
    public static function put($name, $contant)
    {
        return putenv($name.'='.$contant);
    }

    /**
     * Get environement
     *
     * @param $name
     * @return array|false|string
     */
    public static function get($name)
    {
        return getenv($name);
    }
}
