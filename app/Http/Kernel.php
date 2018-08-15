<?php

namespace App\Http;

use Roster\Http\HttpKernel;
use App\Http\Middleware\CheckApp;
use App\Http\Middleware\CheckAuth;
use App\Http\Middleware\CheckChoose;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\OnceEmployee;
use App\Http\Middleware\OnlyEmployee;
use App\Http\Middleware\OnlyEmployer;
use App\Http\Middleware\CheckWorkspace;
use App\Http\Middleware\CheckEmployment;

class Kernel extends HttpKernel
{
    /**
     * This middlewares are running every request
     *
     * @var array
     */
    protected $middleware = [
        TrimStrings::class
    ];

    /**
     * Route middlewares
     *
     * This middlewares are available to use in your routes
     *
     * @var array
     */
    protected $routeMiddleware = [
        'check.app' => CheckApp::class,
        'check.auth' => CheckAuth::class,
        'check.choose' => CheckChoose::class,
        'check.employment' => CheckEmployment::class,
        'check.workspace' => CheckWorkspace::class,
        'only.employee' => OnlyEmployee::class,
        'only.employer' => OnlyEmployer::class,
        'once.employee' => OnceEmployee::class
    ];
}
