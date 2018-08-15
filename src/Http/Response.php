<?php

namespace Roster\Http;

use Roster\Database\Model;

class Response
{
    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var string
     */
    protected $content;

    /**
     * Response constructor.
     * @param $content
     */
    public function __construct($content = '')
    {
        $this->content = $content;
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return $this
     */
    public function prepare()
    {
        foreach ($this->headers as $key => $options)
        {
            list($value, $replace, $code) = array_values($options);

            header($key.': '.$value, $replace, $code);
        }

        echo $this->content;

        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @param bool $replace
     * @param null $code
     * @return $this
     */
    public function header($key, $value, $replace = true, $code = null)
    {
        $this->headers[$key] = [
            'value' => $value,
            'replace' => $replace,
            'code' => $code
        ];

        return $this;
    }

    /**
     * @return Redirect
     */
    public function redirect()
    {
        return new Redirect($this);
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
        $this->header('Content-Type', 'application/json');
        $this->setContent(json_encode($response instanceof Model ? $response->toArray(true) : $response, $option));

        return $this;
    }

    /**
     * View
     *
     * @param $view
     * @param $with
     * @return mixed
     * @throws \Exception
     */
    public function view($view, $with = [])
    {
        $this->setContent(view($view, $with)->render());

        return $this;
    }

    public function abort()
    {
        $this->header('HTTP/1.0 404 Not Found', '');

        return $this;
    }
}
