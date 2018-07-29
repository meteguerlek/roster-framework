<?php

namespace Roster\Mail;

use Swift_SmtpTransport;

class Transport
{
    /**
     * @var object
     */
    private $transport;

    /**
     * @var null
     */
    private static $instance = null;

    /**
     * Transport constructor.
     */
    private function __construct()
    {
        $this->setTransport();
    }

    private function setTransport()
    {
        $driver = config('config.mail.driver'); // dont needed
        $host = config('config.mail.host');
        $port = config('config.mail.port');
        $username = config('config.mail.username');
        $password = config('config.mail.password');

        return $this->transport = (new Swift_SmtpTransport($host, $port))
            ->setUsername($username)
            ->setPassword($password);
    }

    /**
     * Get transport
     *
     * @return object
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Get instance form transport
     *
     * @return bool
     */
    public static function getInstance() {

        if(static::$instance == null)
        {
            static::$instance = new static;
        }

        return static::$instance;
    }
}
