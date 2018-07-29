<?php

namespace Roster\Support;

class Arr
{
    /**
     * Get value from key
     *
     * @param $needly
     * @param $array
     * @return null
     */
    public static function get($needly, $array)
    {
        if (empty($array))
        {
            return null;
        }

        $path = explode('.', $needly);

        $history = null;

        foreach ($path as $value)
        {
            if (isset($array[$value]) && empty($history))
            {
                $history = $array[$value];
            }
            elseif (isset($history[$value]))
            {
                $history = $history[$value];
            }
            elseif (end($path) == $value)
            {
                return $history[$value];
            }
            else
            {
                return false;
            }
        }

        return $history;
    }

    public static function set($array, $key, $value)
    {
        $keys = explode('.', $key);

        $newArray = [];

        foreach ($keys as $key)
        {
            if (isset($array[$key]) && empty($newArray))
            {
                $newArray = $array[$key];
            }
            elseif (end($keys) == $key)
            {
                $newArray[$key] = $value;
            }
            else
            {
                $newArray = $newArray[$key];
            }
        }

        return $newArray;
    }

    /**
     * Check if multidimensional array
     *
     * @param $array
     * @return bool
     */
    public static function isMultiple($array)
    {
        rsort($array);
        return isset($array[0]) && is_array($array[0]);
    }
}