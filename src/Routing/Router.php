<?php

namespace Roster\Routing;

use App\Http\Kernel;
use Closure;
use ReflectionFunction;
use Roster\Auth\Roster\Auth;
use Roster\Http\Middleware;
use Roster\Http\Redirect;
use Roster\Http\Request;
use Roster\Filesystem\File;
use Roster\Http\Response;
use Roster\Logger\Log;
use Roster\View\View;

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
     * @var Route
     */
    protected $currentRoute;

    /**
     * Request method
     *
     * @var null
     */
    protected $currentMethod = null;
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

    public function __construct()
    {
        $this->currentRoute = new Route();
    }

    public function start($method, $arguments)
    {
        if ($method == 'middleware')
        {
            return $this->addMiddlewareGroup(...$arguments);
        }

        $method = strtoupper($method);
        $this->currentRoute->fakeMethod = $method;

        if (in_array($method, $this->allMethods()))
        {
            if($this->isBasic($method))
            {
                $method = 'GET';

                $this->with = isset($arguments[2]) ? $arguments[2] : [];
            }

            return $this->addRoute($arguments[0], $arguments[1], $method);
        }
    }

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
    protected function setMethod($method)
    {
        $this->currentMethod = $method;

        $this->currentRoute->method = $method;

        return $this;
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
     */
    protected function compileRoute($query, $action, $method)
    {
        $this->setAction($action);
        $this->setQuery($query);
        //$this->compileParam();
        $this->setMethod($method);
        $this->setMiddleware();

        static::$routes[$method][$this->currentRoute->query] = $this->currentRoute;
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

            $this->currentRoute->addMiddleware(array_merge(array_keys(static::$group['middleware']), static::$middlewareGroup));
        }
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
        if (Request::isPost() && Request::has('_method'))
        {
            return Request::getValue('_method');
        }

        return static::realMethod();
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
     */
    protected function setAction($action)
    {
        if(is_string($action))
        {
            if(strstr($action, '@'))
            {
                $action = explode('@', $action);

                $this->currentRoute->controller = current($action);
                $this->currentRoute->action = end($action);
            }
            else
            {
                $this->currentRoute->basic = $action;
            }
        }

        if ($action instanceof Closure)
        {
            $this->currentRoute->closure = $action;
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
        $this->currentRoute->original = $query;

        $this->currentRoute->query = $query;

        return $this;
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
        if ($static->isSecured() && $route = $static->findRoute())
        {
            return $static->prepare($route);
        }

        return $static->abort();
    }

    /**
     * @return mixed
     */
    public static function loadRoutes()
    {
        return require_once File::where('routes', 'web')->getPath();
    }

    /**
     * @return bool|Route
     */
    protected function findRoute()
    {
        foreach (static::$routes[static::getMethod()] as $route)
        {
            $this->compileQuery($route);

            if ($route->query === static::$currentQuery)
            {
                return $route;
            }
        }

        return false;
    }

    /**
     * Compile param
     *
     * @param $route
     * @return $this
     */
    protected function compileQuery($route)
    {
        $query = explode('/', $route->query);

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

            $route->query = implode('/', $query);

            $route->params = array_merge($params, Request::all());
        }

        return $this;
    }

    /**
     * Load controller
     *
     * @param Route $route
     * @return mixed
     */
    protected function prepare(Route $route)
    {
        $request = $this->setRequest($route->params);

        $this->runMiddlewares($route->middleware, $request);

        $response = $this->callBack($route, $request);

        return $this->toResponse($response);
    }

    /**
     * @return bool
     */
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

        return true;
    }

    /**
     * @param $response
     * @return mixed|Response|View|void
     */
    protected function toResponse($response)
    {
        if ($response instanceof View)
        {
            $response = new Response($response->render());

            return $response->prepare();
        }
        elseif ($response instanceof Response)
        {
            return $response->prepare();
        }
        elseif ($response instanceof Redirect)
        {
            return $response->prepare();
        }
    }

    /**
     * @param Route $route
     * @param Request $request
     * @return mixed|View
     */
    protected function callBack(Route $route, Request $request)
    {
        if ($route->fakeMethod == 'VIEW')
        {
            return $this->callView($route->basic, $request, $route->with);
        }
        elseif ($route->fakeMethod == 'REDIRECT')
        {
            return $this->callRedirect($route->basic, $route->with);
        }

        // Closure
        if(!is_null($route->closure))
        {
            return $route->callClosure($request);
        }
        // Load controller
        elseif ($route->controller)
        {
            return $route->callController($request);
        }
    }

    /**
     * Get request
     *
     * @param $params
     * @return Request
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
     * @param $routeMiddlewares
     * @param $request
     */
    protected function runMiddlewares($routeMiddlewares, $request)
    {
        $kernel = new Kernel();

        $routeMiddleware = $kernel->getRouteMiddleware();
        $run = [];

        foreach ($routeMiddlewares as $middleware)
        {
            if (array_key_exists($middleware, $routeMiddleware))
            {
               $run[] = $routeMiddleware[$middleware];
            }
        }

        $middlewares = array_merge($kernel->getMiddlewares(), $run);

        foreach ($middlewares as $middleware)
        {
            $middleware = new $middleware();

            $next = next($middlewares);

            if (!$next)
            {
                $next = end($middlewares);
            }

            $middle = function($request) use ($next){
                static $next;
                return $request;
            };

            $response = $middleware->{'handle'}($request, $middle);

            if (!$response instanceof Request)
            {
                return $this->toResponse($response);
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
        if ($callback instanceof Closure)
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
        static::$names[$name] = $this->currentRoute->original;

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
            $this->currentRoute->addMiddleware($middleware);
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
        if ($callback instanceof Closure)
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

    protected function abort()
    {
        header("HTTP/1.0 404 Not Found");

        customView('error'); die;
    }

    protected function isBasic($method)
    {
        return in_array($method, $this->basics, true);
    }

    protected function allMethods()
    {
        return array_merge($this->methods, $this->basics);
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
        return (new static)->start($method, $arguments);
    }
}