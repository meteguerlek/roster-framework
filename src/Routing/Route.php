<?php

namespace Roster\Routing;

use Roster\Http\Request;

class Route
{
    public $query;

    public $original;

    public $fakeMethod;

    public $method;

    public $controller;

    public $middleware = [];

    public $action;

    public $closure;

    public $basic;

    public $with;

    public $params;

    public function addMiddleware($middleware)
    {
        array_push($this->middleware, ...is_array($middleware) ? $middleware : (array) $middleware);

        return $this;
    }

    public function callClosure($request)
    {
        $closure = $this->closure;

        return $closure($request);
    }

    public function callController(Request $request)
    {
        $className = config('app.controllers').str_replace('/', '\\', $this->controller);

        return call_user_func([new $className, $this->action], $request);

    }
}
