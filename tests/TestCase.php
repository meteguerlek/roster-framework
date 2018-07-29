<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Roster\Http\Testing\Response;
use Roster\Sessions\Session;
use Roster\Support\App;

class TestCase extends PHPUnitTestCase
{
    protected $app = null;

    protected $preserveGlobalState = FALSE;
    protected $runTestInSeparateProcess = TRUE;
    protected $backupGlobals = FALSE;
    protected $backupGlobalsBlacklist = ['_SESSION', '_GET', '_POST', '_SERVER', '_REQUEST'];

    public function setUp()
    {
        $this->globalBlacklists();

        $app = new App();
        $app->setBasePath(realpath(__DIR__.'/../'));
        $app->aliases();
        $app->loadRoutes();

        $app->setInstance($this);

        $this->app = $app;
    }

    public function globalBlacklists()
    {
        if ( !isset($_SESSION))
            $_SESSION = [];

        if ( !isset($_GET))
            $_GET = [];

        if ( !isset($_POST))
            $_POST = [];

        if ( !isset($_SERVER))
            $_SERVER = [];

        if ( !isset($_REQUEST))
            $_REQUEST = [];

        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    public function get($route)
    {
        $response = new Response();
        $response->get($route);

        return $response;
    }

    public function post($route)
    {
        $response = new Response();
        $response->get($route);

        return $response;
    }

    public function withSession($key, $value)
    {
        Session::set($key, $value);

        return $this;
    }

    public function withOutSession($key)
    {
        Session::unset($key);

        return $this;
    }
}
