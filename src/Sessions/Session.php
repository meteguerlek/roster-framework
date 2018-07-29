<?php

namespace Roster\Sessions;

use Roster\Support\Arr;

class Session
{
    /**
     * @var array
     */
    protected static $jobs = [];

    /**
     * Set sessions
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public static function set($key, $value)
    {
        return $_SESSION[$key] = $value;
    }

    /**
     * Get session from key
     *
     * @param $key
     * @return mixed
     */
    public static function get($key)
    {
        return Arr::get($key, $_SESSION);
    }

    /**
     * Get all
     *
     * @return mixed
     */
    public static function all()
    {
        return $_SESSION;
    }

    /**
     * Check if sessions isset
     *
     * @param $key
     * @return bool
     */
    public static function has($key)
    {
        return Arr::get($key, $_SESSION) ? true : false;;
    }

    /**
     * Unset sessions
     *
     * @param $key
     */
    public static function unset($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * Clear all sessions
     * @return bool
     */
    public static function forgetAll()
    {
        return session_destroy();
    }

    /**
     * Start sessions
     *
     * @return bool
     */
    public static function start()
    {
        return session_start();
    }

    /**
     * Sessions jobs
     *
     */
    public static function jobs()
    {
        foreach(array_merge(config('app.forgets'), static::$jobs) as $forget)
        {
            static::unset($forget);
        }
    }

    /**
     * Add jobs
     *
     * @param $value
     */
    public static function job($value)
    {
        static::$jobs[] = $value;
    }
}