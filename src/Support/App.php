<?php

namespace Roster\Support;

use Carbon\Carbon;
use Roster\Routing\Router;
use Roster\Sessions\Session;

class App
{
    /**
     * @var null
     */
    protected $basePath = null;

    /**
     * @var bool
     */
    protected $errorReporting = true;

    /**
     * @var null
     */
    protected $locale = null;

    /**
     * @var null
     */
    protected static $instance = null;

    /**
     * Boot app
     *
     * @throws \Exception
     */
    public function boot()
    {
        $this->sessionStart();

        $this->setErrorReporting(grl('DEBUG'));

        $this->errorHandling();

        $this->aliases();

        $this->runRoutes();

        $this->runJobs();

        $this->setInstance($this);
    }

    /**
     * Error reporting
     *
     */
    public function errorHandling()
    {
        error_reporting($this->errorReporting ? -1 : 0);
    }

    /**
     * Set error reporting
     *
     * @param $bool
     */
    public function setErrorReporting($bool)
    {
        $this->errorReporting = $bool;
    }

    /**
     * Defines
     *
     * @param $key
     * @param $value
     */
    public function define($key, $value)
    {
        define($key, $value);
    }

    /**
     * App aliases
     *
     * @throws \Exception
     */
    public function aliases()
    {
        foreach(config('app.aliases') as $name => $namespace)
        {
            class_alias($namespace, $name);
        }
    }

    /**
     *
     */
    public function sessionStart()
    {
        Session::start();
    }

    /**
     * Routes
     *
     */
    public function runRoutes()
    {
        Router::run();
    }

    /**
     * Routes
     *
     */
    public function loadRoutes()
    {
        Router::loadRoutes();
    }

    /**
     * App jobs
     */
    public function runJobs()
    {
        Session::jobs();
    }

    /**
     * Set base path
     *
     * @param $path
     */
    public function setBasePath($path)
    {
        $this->basePath = $path;

        $this->define('ABSPATH', $path);
    }

    /**
     * @return bool
     */
    public static function getLocale()
    {
        return Config::get('app.locale');
    }

    /**
     * @param $locale
     */
    public static function setLocale($locale)
    {
        Config::set('app.locale', $locale);
        setlocale(LC_ALL, $locale);
    }

    /**
     * @param $locale
     * @return bool
     */
    public static function isLocale($locale)
    {
        return Config::get('app.locale') == $locale;
    }

    /**
     * @param $instance
     */
    public function setInstance($instance)
    {
        static::$instance = $instance;
    }
}
