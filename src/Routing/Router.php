<?php

namespace Roster\Routing;

use App\Http\Kernel;
use ReflectionFunction;
use Roster\Auth\Roster\Auth;
use Roster\Http\Request;
use Roster\Filesystem\File;

class Router
{
    /**
     * @var array
     */
    protected static $routes = [];

    /**
     * @var array
     */
    protected static $names = [];

    /**
     * @var array
     */
    protected static $group = [];

    /**
     * @var array
     */
    protected static $middlewareGroup = [];

    /**
     * @var null
     */
    protected static $currentQuery = null;

    /**
     * @var Request
     */
    protected static $request = null;

    /**
     * @var null
     */
    protected $query = null;

    /**
     * @var null
     */
    protected $original = null;

    /**
     * @var null
     */
    protected $action = null;

    /**
     * Request method
     *
     * @var null
     */
    protected $currentMethod = null;

    /**
     * @var array
     */
    protected $middleware = [];

    /**
     * @var null
     */
    protected $basic = null;

    /**
     * @var null
     */
    protected $controller = null;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var null
     */
    protected $function = null;

    /**
     * @var array
     */
    protected $with = [];

    /**
     * @var null
     */
    protected $fakeMethod = null;

    /**
     * @var array
     */
    protected $methods = [
        'GET', 'POST', 'PUT',
        'PATCH', 'DELETE', 'OPTIONS'

    ];

    /**
     * @var array
     */
    protected $basics = [
        'VIEW', 'REDIRECT'
    ];

    /**
     * Set current query
     *
     * @return null|string|string[]
     */
    protected static function setCurrentQuery()
    {
        if (!static::$currentQuery)
        {
            return static::$currentQuery = (new static)->parse(Request::query());
        }
    }

    /**
     * @param $method
     * @return mixed
     */
    protected function setCurrentMethod($method)
    {
        return $this->currentMethod = $method;
    }

    /**
     * @return null
     */
    public static function getCurrentQuery()
    {
        return static::$currentQuery;
    }

    /**
     * Add route
     *
     * @param $query
     * @param $action
     * @param $method
     * @return $this
     */
    protected function addRoute($query, $action, $method)
    {
        // Check prefix
        if(!empty(static::$group['prefix']))
        {
            $query = implode('', static::$group['prefix']). '/' . $this->parse($query);
        }

        $this->compileRoute($this->parse($query), $action, $method);

        return $this;
    }

    /**
     * Compile routes
     *
     * @param $query
     * @param $action
     * @param $method
     * @return array
     */
    protected function compileRoute($query, $action, $method)
    {
        $this->setAction($action, $query);
        $this->setQuery($query);
        $this->compileParam();
        $this->setCurrentMethod($method);
        $this->setMiddleware();

        return static::$routes[$method][$this->query] = [
            'query' => $this->query,
            'method' => $method,
            'fakeMethod' => $this->fakeMethod,
            'controller' => $this->controller,
            'action' => $this->action,
            'middleware' => $this->middleware,
            'function' => $this->function,
            'basic' => $this->basic,
            'with' => $this->with,
            'params' => $this->params,
        ];
    }

    /**
     * Set middleware
     *
     * @return mixed|string
     */
    protected function setMiddleware()
    {
        if (isset(static::$group['middleware']))
        {
            if (empty(static::$group['middleware']))
            {
                return false;
            }

            $urls = array_values(static::$group['middleware']);
            $url = end($urls);

            if (!in_array($url, static::$group['prefix']))
            {
                static::$group['middleware'] = array_unique(static::$group['middleware']);

                array_pop(static::$group['middleware']);
            }

            $middleware = array_merge(array_keys(static::$group['middleware']), static::$middlewareGroup);

            $this->middleware = $middleware;
        }
    }

    /**
     * Run routes
     *
     * @return \Roster\View\View
     */
    public static function run()
    {
        $static = new static;
        $static::setCurrentQuery();

        if (!$static->checkCache())
        {
            static::loadRoutes();
        }
        // First check if route defined
        // Second, we check here if a request for post variable exists
        // and if csrf is corect
        if ($static->routeExist() && $static->isSecured())
        {
            return $static->loadController(static::$routes[static::getMethod()][static::$currentQuery]);
        }

        return $static->abort();
    }

    public static function loadRoutes()
    {
        return require_once File::where('routes', 'web')->getPath();
    }

    protected function checkCache()
    {
        $file = File::where(config('disk.storage.cache'), 'routes');

        if (!$file->exist())
        {
            return false;
        }

        $cache = require $file->getPath();

        static::$routes = $cache['routes'];
        static::$names = $cache['names'];
        $method = static::getMethod();

        static::$routes[$method] = [];

        foreach ($cache['routes'][$method] as $route)
        {
            $this->query = $route['query'];
            $this->compileParam();
            $old = $cache['routes'][$method][$route['query']];

            static::$routes[$method][$this->query] = array_merge($old, [
                'query' => $this->query,
                'params' => $this->params
            ]);

            if ($this->query == static::$currentQuery)
            {
                return true;
            }
        }

        return true;
    }

    /**
     * Check if routes exist
     *
     * @return bool
     */
    protected function routeExist()
    {
        return array_key_exists(static::$currentQuery, static::$routes[static::getMethod()])
            ? true
            : false;
    }

    /**
     * @return mixed
     */
    protected static function realMethod()
    {
        return Request::method();
    }

    /**
     * @return mixed
     */
    public static function getMethod()
    {
        return Request::has('_method')
            ? Request::getValue('_method')
            : static::realMethod();
    }

    /**
     * Secure the routes
     *
     * @return bool
     */
    protected function isSecured()
    {
        if (static::realMethod() == 'POST')
        {
            if (Request::isset() && Request::checkCsrf())
            {
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * Parse query
     *
     * @param $query
     * @return null|string|string[]
     */
    protected function parse($query)
    {
        $query = preg_replace('/\&(.*)/', '', $query);

        $query = trim($query, '/');

        return $query;
    }

    /**
     * Set action
     *
     * @param $action
     * @return $this
     */
    protected function setAction($action, $query)
    {
        if(is_string($action))
        {
            if(strstr($action, '@'))
            {
                $action = explode('@', $action);
                $controller = current($action);
                $method = end($action);

                $this->controller = $controller;
                $this->action = $method;
            }
            else
            {
                $this->basic = $action;
            }
        }

        if ($action instanceof \Closure)
        {
            $this->function = $action;
        }
    }

    /**
     * Set query
     *
     * @param $query
     * @return $this
     */
    protected function setQuery($query)
    {
        $this->original = $query;

        $this->query = $query;

        return $this;
    }

    /**
     * Compile param
     *
     * @return $this
     */
    protected function compileParam()
    {
        $query = explode('/', $this->query);

        $currentQuery = explode('/', static::$currentQuery);

        //check if query and current query are the same
        if (count($query) == count($currentQuery))
        {
            //check the differenz between uri and Query
            $diff = array_diff($query, $currentQuery);

            $params = [];

            foreach ($diff as $k => $d)
            {
                //set only values with brace
                if (preg_match('/{(.*?)}/', $d))
                {
                    $key = preg_replace('/[{}]/', '' , $d);

                    $query[$k] = $currentQuery[$k];
                    $params[$key] = $currentQuery[$k];
                }
            }

            $this->query = implode('/', $query);

            $this->params = array_merge($params, Request::all());
        }

        return $this;
    }

    /**
     * Load controller
     *
     * @param $route
     * @return mixed
     */
    protected function loadController($route)
    {
        $request = $this->setRequest($route['params']);

        // View / Redirect
        if(in_array($route['fakeMethod'], $this->basics))
        {
            if ($route['fakeMethod'] == 'VIEW')
            {
                return $this->callView($route['basic'], $request, $route['with']);
            }
            elseif ($route['fakeMethod'] == 'REDIRECT')
            {
                return $this->callRedirect($route['basic'], $route['with']);
            }

            return $this->abort();
        }

        // Closure
        if(!is_null($route['function']))
        {
            return $route['function']($request);
        }

        $className = config('app.controllers').str_replace('/', '\\', $route['controller']);
        $class = new $className();

        $this->runMiddlewares($route['middleware'], $request);

        // Load controller
        return $class->{$route['action']}($request);
    }

    /**
     * Get request
     *
     * @param $params
     */
    protected function setRequest($params)
    {
        Request::setParams($params);
        Request::setUser(Auth::user());

        return Request::instance();
    }

    /**
     * Run middlewares
     *
     * @param $middlewares
     * @param $request
     * @throws \Exception
     */
    protected function runMiddlewares($middlewares, $request)
    {
        $kernel = new Kernel();

        foreach ($kernel->getMiddlewares() as $middleware)
        {
            $middleware = new $middleware();
            $middleware->{'handle'}($request);
        }

        $routeMiddleware = $kernel->getRouteMiddleware();

        foreach ($middlewares as $middleware)
        {
            if (array_key_exists($middleware, $routeMiddleware))
            {
                $middleware = new $routeMiddleware[$middleware]();

                $middleware->{'handle'}($request);
            }
        }
    }

    /**
     * Group route
     *
     * @param $group
     * @param $callback
     * @return void
     */
    public static function group($group, $callback)
    {
        if ($callback instanceof \Closure)
        {
            if (isset($group['prefix']))
            {
                static::$group['prefix'][] = $group['prefix'];
            }

            if (isset($group['middleware']))
            {
                if (is_array($group['middleware']))
                {
                    foreach ($group['middleware'] as $middleware)
                    {
                        static::$group['middleware'][$middleware] = $group['prefix'];
                    }
                }
                else
                {
                    static::$group['middleware'][$group['middleware']] = $group['prefix'];
                }
            }

            $callback();

            if (isset($group['middleware']))
            {
                array_pop(static::$group['middleware']);
            }

            array_pop(static::$group['prefix']);
        }
    }

    /**
     * Load the view directly over the route
     *
     * @param $view
     * @param Request $request
     * @param array $with
     * @return \Roster\View\View
     * @throws \Exception
     */
    protected function callView($view, Request $request, $with = [])
    {
        return view($view, array_merge(['request' => $request], $with));
    }

    /**
     * Redirection
     *
     * @param $redirection
     * @param int $with
     * @return mixed
     */
    protected function callRedirect($redirection, $with = 301)
    {
        return redirect($redirection, $with);
    }

    /**
     * Set name
     *
     * @param $name
     * @return $this
     */
    public function name($name)
    {
        static::$names[$name] = $this->original;

        return $this;
    }

    /**
     * Middleware
     *
     * @return mixed
     */
    public function addMiddleware()
    {
        $middlewares = func_get_args();

        foreach ($middlewares as $middleware)
        {
            static::$routes[$this->currentMethod][$this->query]['middleware'][] = $middleware;
        }

        return $this;
    }

    /**
     * Middleware group
     *
     * @param $method
     * @param $callback
     */
    protected function addMiddlewareGroup($method, $callback)
    {
        if ($callback instanceof \Closure)
        {
            static::$middlewareGroup += (array) $method;

            $callback();

            static::$middlewareGroup = [];
        }
    }

    /**
     * Get name from routes
     *
     * @param $name
     * @return null
     */
    public static function getName($name)
    {
        return array_key_exists($name, static::$names)
            ? static::$names[$name]
            : null;
    }

    /**
     * Get routes
     *
     * @return array
     */
    public static function getRoutes()
    {
        return static::$routes;
    }

    /**
     * Get all names
     *
     * @return array
     */
    public static function getNames()
    {
        return static::$names;
    }

    /**
     * Check if method exist
     *
     * @param $class
     * @param $method
     */
    protected function checkIfMethodExist($class, $method)
    {
        if (!class_exists(get_class($class)))
        {
            throw new \Exception('The Class ' . $method . ' not exist!');
        }

        if (!method_exists($class, $method))
        {
            throw new \Exception('The Method ' . $method . ' not exist!');
        }
    }

    protected function abort()
    {
        header("HTTP/1.0 404 Not Found");

        customView('error'); die;
    }

    public function __call($method, $arguments)
    {
        if ($method == 'middleware')
        {
            return $this->addMiddleware(...$arguments);
        }

        return $this->{$method}(...$arguments);
    }

    /**
     * @param $method
     * @param $arguments
     * @return Router
     */
    public static function __callStatic($method, $arguments)
    {
        $static = new static;

        if ($method == 'middleware')
        {
            return $static->addMiddlewareGroup(...$arguments);
        }

        $method = strtoupper($method);
        $static->fakeMethod = $method;

        if (in_array($method, array_merge($static->methods, $static->basics)))
        {
            if(in_array($method, $static->basics, true))
            {
                $method = 'GET';

                $static->with = isset($arguments[2]) ? $arguments[2] : [];
            }

            return $static->addRoute($arguments[0], $arguments[1], $method);
        }

        throw new \Exception("Method {$method} not exist");
    }
}