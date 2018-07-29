<?php

namespace Roster\Support;

use Roster\Sessions\Session;

class Input
{
    /**
     * Has input
     *
     * @param $key
     * @return bool
     */
    public static function has($key)
    {
        if (Session::has('inputs'))
        {
            $inputs = Session::get('inputs');

            return isset($inputs[$key])
                ? true
                : false;
        }

        return false;
    }

    /**
     * Get input
     *
     * @param $key
     * @return mixed
     */
    public static function get($key)
    {
        return Session::get('inputs')[$key];
    }

    /**
     * Get all
     *
     * @return array|mixed
     */
    public static function all()
    {
        return Session::has('inputs')
            ? Session::get('inputs')
            : [];
    }

    /**
     * Put inputs
     *
     * @param $inputs
     * @param bool $withOldInputs
     * @return mixed
     */
    public static function put($inputs, $withOldInputs = true)
    {
        $oldInputs = $withOldInputs ? static::all() : [];

        $oldInputs += $inputs;

        return Session::set('inputs', $oldInputs);
    }

    /**
     * Clear all inputs
     *
     * @return bool
     */
    public static function clear()
    {
        return Session::unset('inputs');
    }
}
