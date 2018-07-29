<?php

namespace Roster\Mail;

use Swift_Mailer;
use Swift_Message;

class Mail
{
    /**
     * @var object
     */
    protected $transport;

    /**
     * @var array|string
     */
    protected $from;

    /**
     * @var array
     */
    protected $to;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $body;

    /**
     * Mail constructor.
     */
    public function __construct()
    {
        $this->setTransport();
    }

    /**
     * Set subject
     *
     * @param $subject
     * @return mixed
     */
    public static function subject($subject)
    {
        $static = new static;

        $static->subject = $subject;

        return $static;
    }

    /**
     * Set adresses
     *
     * @param $addresses
     * @return Mail
     */
    public function from($addresses)
    {
        $this->from = $addresses;

        return $this;
    }

    /**
     * Set to
     *
     * @param $addresses
     * @return mixed
     */
    public function to($addresses)
    {
        $this->to = $addresses;

        return $this;
    }

    /**
     * Set body
     *
     * @param $body
     * @return mixed
     */
    public function body($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Send the message
     *
     * @return int
     */
    public function send()
    {
        // Create the Transport
        $mailer = new Swift_Mailer($this->transport);

        // Create a message
        $message = (new Swift_Message($this->subject))
            ->setFrom($this->from)
            ->setTo($this->to)
            ->setBody($this->body);

        // Send the message
        $result = $mailer->send($message);

        return $result;
    }

    /**
     * Set transport
     *
     * @return mixed
     */
    public function setTransport()
    {
        $instance = Transport::getInstance();

        $transport = $instance->getTransport();

        return $this->transport = $transport;
    }
}