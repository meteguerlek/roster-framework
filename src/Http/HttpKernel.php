<?php

namespace Roster\Http;


class HttpKernel
{
    /**
     * @var array
     */
    protected $middleware = [];

    /**
     * @var array
     */
    protected $routeMiddleware = [];

    /**
     * @return array
     */
    public function getMiddlewares()
    {
        return $this->middleware;
    }

    /**
     * @return array
     */
    public function getRouteMiddleware()
    {
        return $this->routeMiddleware;
    }
}
