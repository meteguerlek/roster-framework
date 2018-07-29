<?php

namespace Roster\Support;

use Roster\Filesystem\File;

class Grl
{
    /**
     * @var null
     */
    private $grl = null;

    /**
     * @var null
     */
    private static $instance = null;

    /**
     * Connection constructor.
     */
    private function __construct()
    {
        $this->setGrl();
    }

    /**
     * Set grl
     *
     * @return $this
     */
    public function setGrl()
    {
        $grl = File::where('', '.', 'grl')->getContent();

        $toArray = explode("\n", wordwrap($grl, 0, "\n", false));

        $toArray = array_filter($toArray, function($value){return strlen($value) !== 1;});

        $matches = [];

        foreach($toArray as $v)
        {
            $match = explode('=', $v);

            $key = $match[0];

            $value = preg_replace("/\r|\n/", "", isset($match[1]) ? $match[1] : '');

            $matches[$key] = $this->parseValue($value);
        }

        $this->grl = $matches;

        return $this;
    }

    /**
     * Parse value
     *
     * @param $value
     * @return bool|int
     */
    protected function parseValue($value)
    {
        if ($value == 'true')
        {
            return true;
        }
        elseif ($value == 'false')
        {
            return false;
        }
        elseif(is_numeric($value))
        {
            return (int) $value;
        }

        return $value;
    }

    /**
     * Get config
     *
     * @param $config
     * @param string $default
     * @return string|void
     */
    public function get($config, $default = '')
    {
        if (array_key_exists($config, $this->grl))
        {
            return $this->grl[$config];
        }
        else
        {
            return $default;
        }

        return false;
    }

    /**
     * Get instance form grl
     *
     * @return bool
     */
    public static function getInstance()
    {
        if(static::$instance == null)
        {
            static::$instance = new static;
        }

        return static::$instance;
    }
}
