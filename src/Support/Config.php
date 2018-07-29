<?php

namespace Roster\Support;

use Roster\Filesystem\File;

class Config
{
    /**
     * @var array
     */
    private $configs = [];

    /**
     * @var null
     */
    private static $instance = null;

    /**
     * Set config
     *
     * @param $file
     * @param $directory
     * @return $this
     */
    protected function setConfig($file, $directory)
    {
        $this->configs[$file] = require File::where($directory, $file)->getPath();

        return $this;
    }

    /**
     * Get config
     *
     * @param $config
     * @return string|void
     */
    protected function getConfig($config)
    {
        return Arr::get($config, $this->configs);
    }

    /**
     * Get
     *
     * @param $config
     * @param $directory
     * @return bool
     */
    public static function get($config, $directory = 'config')
    {
        $config = explode('.', $config);

        $file = current($config);

        if(static::$instance == null || !isset(static::$instance->configs[$file]))
        {
            static::$instance = method_exists(static::$instance, 'setConfig')
                ? static::$instance->setConfig($file, $directory)
                : (new static)->setConfig($file, $directory);
        }

        return static::$instance->getConfig(implode('.', $config));
    }

    /**
     * @param $config
     * @return bool|null
     */
    public static function has($config)
    {
        if (!static::$instance)
        {
            return false;
        }

        return Arr::get($config, static::$instance->configs);
    }

    /**
     * @param $key
     * @param $value
     */
    public static function set($key, $value)
    {
        static::get($key);

        $config = explode('.', $key);

        $file = current($config);

        static::$instance->configs[$file] = Arr::set(static::$instance->configs, $key, $value);

    }
}
