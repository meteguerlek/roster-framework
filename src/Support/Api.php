<?php

namespace Roster\Support;

use Roster\Database\Model;

class Api
{
    /**
     * Output
     *
     * @var array
     */
    protected $api = [];

    /**
     * Convert object to array and get results
     *
     * @return static
     */
    public static function make()
    {
        return new static;
    }

    /**
     * Get as array
     *
     * @param $response
     * @return array
     */
    public function array($response)
    {
        return print_r($response instanceof Model ? $response->toArray(false) : $response);
    }

    /**
     * Get as json
     *
     * @param $response
     * @param bool $option
     * @return string
     */
    public function json($response, $option = true)
    {
        $json = json_encode($response instanceof Model ? $response->toArray(true) : $response, $option);

        echo $json;

        return $json;
    }

    /**
     * View
     *
     * @param $view
     * @param $with
     * @return mixed
     * @throws \Exception
     */
    public function view($view, $with)
    {
        return print_r(view($view, $with)->html());
    }
}
