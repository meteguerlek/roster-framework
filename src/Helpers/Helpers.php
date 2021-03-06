<?php

use Roster\Container\Container;
use Roster\Http\Response;

use Roster\Support\App;
use Roster\View\HtmlString;
use Roster\View\View;
use Roster\Support\Grl;
use Roster\Http\Request;
use Roster\Support\Input;
use Roster\Routing\Router;
use Roster\Support\Config;
use Roster\Filesystem\File;
use Roster\Sessions\Session;


/**
 * Get controller
 *
 * @param $class
 * @param string $namespace
 * @return mixed
 */
function getClass($class, $namespace = '')
{
    if (preg_match('/[.]/', $class))
    {
        $path = explode('.', $class);

        $namespace .= $namespace ? '\\'. implode('\\', $path) : implode('\\', $path);
    }
    else
    {
        $namespace .= $namespace ? '\\' . $class : $class;
    }

    return new $namespace();
}

/**
 * Get config information
 *
 * @param $config
 * @param $directory
 * @return string
 */
function config($config, $directory = 'config')
{
    return Config::get($config, $directory);
}

/**
 * Get language
 *
 * @param $get
 * @param $options
 * @return string
 */
function __($get, array $options = [])
{
    $language = App::getLocale();

    $languageDirectory = config('disk.lang'). '.'. $language;

    $text =  config($get, $languageDirectory);

    foreach ($options as $key => $option)
    {
        $text = str_replace(':'.$key, $option, $text);
    }

    return $text;
}

/**
 * Dump and die
 *
 * @return void
 */
function dd()
{
    foreach (func_get_args() as $content)
    {
        print('<pre>'.print_r($content,true).'</pre>');
    }

    die;
}

/**
 * View and die
 *
 * @param $view
 * @throws Exception
 */
function abort($view)
{
    header("HTTP/1.0 404 Not Found");

    view($view); die;
}

/**
 *
 *
 * @param $content|HtmlString
 * @param bool $dobleEncode
 * @return string
 */
function e($content, $dobleEncode = true)
{
    if ($content instanceof HtmlString)
    {
        return $content;
    }

    return htmlspecialchars($content, ENT_QUOTES, 'UTF-8', $dobleEncode);
}

/**
 * Load view
 *
 * @param $template
 * @param array $variables
 * @return View
 */
function view($template, $variables = [])
{
    return new View($template, $variables);
}

/**
 * Response comimg soon
 *
 * @param string $response
 * @return Response
 */
function response($response = '')
{
    return new Response($response);
}

/**
 * Load custom view
 *
 * @param $template
 * @param array $variables
 * @return View
 * @throws Exception
 */
function customView($template, $variables = [])
{
    if (File::where(config('disk.view'), $template)->exist())
    {
        return new View($template, $variables);
    }

    return new View($template, $variables, config('disk.customView'));
}

/**
 * Get value from array
 *
 * @param $array
 * @return array|mixed
 */
function value($array)
{
    $values = [];

    if (is_array($array))
    {
        foreach ($array as $key => $value)
        {
            $values[] = $value;
        }

        if (count($values) - 1 == 0)
        {
            return $values[0];
        }

        return $values;
    }

    return $values;
}

/**
 * Grl configuration
 *
 * @param $config
 * @param string $default
 * @return string
 * @throws Exception
 */
function grl($config, $default = '')
{
    $grl = Grl::getInstance();

    return $grl->get($config, $default);
}


/**
 * Get inputs from session
 *
 * @param $key
 * @param string $default
 * @return mixed
 */
function old($key, $default = '')
{
    if (Input::has($key))
    {
        return Input::get($key);
    }
    elseif ($default && !Session::has('inputs'))
    {
        return $default;
    }

    return false;
}

/**
 * @param $key
 * @return mixed
 */
function request($key)
{
    return Request::instance()->{$key};
}

/**
 * Compile url
 *
 * @param $query
 * @return string
 * @throws Exception
 */
function url($query = '')
{
    // Delete last slash from app url
    $appUrl = preg_replace('/\/$/', '', config('app.app_url'));

    // Delete first and last slash from query
    $query = preg_replace('/^\/|\/$/', '', $query);

    return $appUrl.'/'.$query;
}

/**
 * Generate token field
 *
 * @return string
 * @throws Exception
 */
function csrf()
{
    return '<input type="hidden" name="_token" value="'.Request::token().'">';
}

/**
 * @return mixed
 * @throws Exception
 */
function csrf_token()
{
    return Request::token();
}

/**
 * Redirect
 *
 * @param $redicton
 * @param int $code
 * @return \Roster\Http\Redirect
 */
function redirect($redicton, $code = 301)
{
    return (new Response())
        ->header('Location', $redicton, true, $code)
        ->redirect();
}

/**
 * Back
 *
 */
function back()
{
    if (isset($_SERVER['HTTP_REFERER']))
    {
        return redirect($_SERVER['HTTP_REFERER']);
    }

    return redirect('/');
}

/**
 * Get route from name
 *
 * @param $name
 * @param array $parameters
 * @return string
 */
function route($name, $parameters = [])
{
    $routeName = Route::getName($name);

    foreach ($parameters as $key => $parameter)
    {
        if (strpos($routeName, '{'.$key.'}'))
        {
            $routeName = str_replace('{'.$key.'}', $parameter, $routeName);

            continue;
        }

        $routeName .= '?'.$key.'='.$parameter;

    }

    return url($routeName);
}