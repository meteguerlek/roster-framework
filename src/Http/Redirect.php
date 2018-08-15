<?php

namespace Roster\Http;

class Redirect
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * Redirect constructor.
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     *
     */
    public function prepare()
    {
        $this->response->prepare(); exit;
    }
}
