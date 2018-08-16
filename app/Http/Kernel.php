<?php

namespace App\Http;

use Roster\Http\HttpKernel;

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
    ];
}
