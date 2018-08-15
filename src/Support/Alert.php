<?php

namespace Roster\Support;

use Roster\Sessions\Session;

class Alert
{
    /**
     * @var array
     */
    protected $alerts = [
        'error', 'success'
    ];

    /**
     * Set errors
     *
     * @param $name
     * @param $alert
     * @return mixed
     */
    protected function set($name, $alert)
    {
        $old = [];

        $name = Str::plural($name);

        if (Session::has($name))
        {
            $old = Session::get($name);
        }

        if (!is_array($alert))
        {
            $old['alert'][] = $alert;
        }
        else
        {
            $old += $alert;
        }


        Session::set($name, $old);

        return $this;
    }

    /**
     * Set input
     *
     * @param $name
     * @return $this
     */
    public function setInput(array $name)
    {
        $history = [];

        if (Session::has('inputs'))
        {
            $history = Session::get('inputs');
        }

        Session::set('inputs', array_merge($history, $name));

        return $this;
    }

    /**
     * Get all errors
     *
     * @param $alert
     * @return mixed|string
     */
    public static function all($alert = null)
    {
        if ($alert) return Session::has($alert) ? Session::get($alert) : [];

        $errors = Session::has('errors') ? Session::get('errors') : [];
        $successes = Session::has('successes') ? Session::get('successes') : [];

        return array_merge(['errors' => array_values($errors)], ['successes' => array_values($successes)]);
    }

    /**
     * Check error
     * If isset return the error message
     * or return empty string
     *
     * @param array $names
     * @param bool $output
     * @param string $errorName
     * @return string
     */
    public static function hasError($names, $output = false, $errorName = 'errors')
    {
        if (Session::has($errorName))
        {
            $errors = Session::get($errorName);

            foreach ((array) $names as $name)
            {
                if (isset($errors[$name]))
                {
                    return $output;
                }
            }
        }

        return false;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $static = new static;

        if (in_array($name, $static->alerts))
        {
            return $static->set($name, ...$arguments);
        }
    }
}
