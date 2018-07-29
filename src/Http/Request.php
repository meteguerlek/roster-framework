<?php

namespace Roster\Http;

use App\Models\User;
use Roster\Auth\Generate;
use Roster\Auth\Roster\Auth;
use Roster\Sessions\Session;

class Request
{
    /**
     * @var array
     */
    protected static $request = [];

    /**
     * @var User
     */
    protected static $user = false;

    /**
     * @var string
     */
    protected $file = null;

    /**
     * Set request
     *
     * @param Request $request
     */
    public static function setParams($request)
    {
        static::$request = $request;
    }

    /**
     * @param $key
     * @param $value
     */
    public static function add($key, $value)
    {
        static::$request[$key] = $value;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function input($name)
    {
        return $this->__get($name);
    }

    /**
     * Check if input isset
     *
     * @param $key
     * @return bool
     */
    public function hasInput($key)
    {
        return array_key_exists($key, static::$request)
            ? true
            : false;
    }

    /**
     * Has inputs
     *
     * @param array $keys
     * @return bool
     */
    public function hasInputs(array $keys)
    {
        foreach ($keys as $key)
        {
            if (!$this->hasInput($key))
            {
                return false;
            }
        }

        return true;
    }

    public function mergeInputs(...$inputs)
    {
        $array = [];

        foreach ($inputs as $input)
        {
            if ($this->{$input})
            {
                if (is_array($this->{$input}))
                {
                    $array += $this->{$input};
                }
                else
                {
                    $array[] = $this->{$input};
                }
            }
        }

        return $array;
    }

    /**
     * Set file
     *
     * @param string $key
     * @return null|Request
     */
    public function file($key)
    {
        return array_key_exists($key, $_FILES)
            ? (object) $_FILES[$key]
            : null;
    }

    /**
     * Get all files
     *
     * @return mixed
     */
    public function files()
    {
        return $_FILES;
    }

    /**
     * Check if files set
     *
     * @param $key
     * @return bool|null
     */
    public function hasFile($key = '')
    {
       if (!empty($key))
       {
           return array_key_exists($key, $_FILES)
               ? !$_FILES[$key]['error'] ? true : false
               : false;
       }

       return !empty($_FILES)
           ? true
           : false;
    }


    /**
     * Get $_GET
     *
     * @return mixed
     */
    public static function get()
    {
        array_shift($_GET);

        return $_GET;
    }

    /**
     * Get $_POST
     *
     * @return mixed
     */
    public static function post()
    {
        return $_POST;
    }

    /**
     * Get $_COOKIE
     *
     * @return mixed
     */
    public static function cookie()
    {
        return $_COOKIE;
    }

    /**
     * Get $_GET and $_POST
     *
     * @return mixed
     */
    public static function all()
    {
        return array_merge($_REQUEST, $_FILES);
    }

    /**
     * Check if request has the following key
     *
     * @param $key
     * @return bool
     */
    public static function has($key)
    {
        return isset($_REQUEST[$key])
            ? true
            : false;
    }

    /**
     * Check if request filled
     *
     * @return bool
     */
    public static function isset()
    {
        return count($_REQUEST) > 1
            ? true
            : false;
    }

    /**
     * Get request value with key
     *
     * @param $key
     * @return mixed
     */
    public static function getValue($key)
    {
        return $_REQUEST[$key];
    }

    /**
     * Get query string
     *
     * @return mixed
     */
    public static function query()
    {
        return isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : false;
    }

    /**
     * Get request method
     *
     * @return mixed
     */
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @return bool
     */
    public static function isPost()
    {
        return static::method() == 'POST';
    }

    /**
     * Get uri
     *
     * @return mixed
     */
    public static function uri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Check if csrf token isset
     *
     * @return bool
     */
    public static function checkCsrf()
    {
        if (Request::has('_token') && Session::has('_token'))
        {
            if (Request::getValue('_token') == Session::get('_token'))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate csrf token
     *
     * @return mixed
     */
    public static function token()
    {
        return Session::has('_token')
            ? Session::get('_token')
            : Session::set('_token', Generate::token());
    }

    /**
     * @return User
     */
    public function user()
    {
        return static::$user;
    }

    /**
     * @param User $user
     *
     * @return User
     */
    public static function setUser($user)
    {
        return static::$user = $user;
    }

    /**
     * @return Request
     */
    public static function instance()
    {
        return new static();
    }

    /**
     * Is ajax
     *
     * @return boolean
     */
    public function isAjax()
    {
        if (!isset($_SERVER["HTTP_X_REQUESTED_WITH"]))
        {
            return false;
        }

        return $_SERVER["HTTP_X_REQUESTED_WITH"] == 'XMLHttpRequest';
    }

    /**
     * Get request variables
     *
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return array_key_exists($name, static::$request)
            ? static::$request[$name]
            : null;
    }

    /**
     * Set request
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        static::$request[$name] = $value;
    }
}