<?php

namespace Roster\Mail;

use Swift_Mailer;
use Swift_Message;

class Mailable
{
    /**
     * @var array|string
     */
    protected $from = [];

    /**
     * @var array
     */
    protected $to = [];

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var string
     */
    protected $view;

    /**
     * Set subject
     *
     * @param $subject
     * @return mixed
     */
    public function subject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set adresses
     *
     * @param $address
     * @param null $name
     * @return Mailable
     */
    public function from($address, $name = null)
    {
        $this->from[$address] = $name;

        return $this;
    }

    /**
     * Set to
     *
     * @param $address
     * @param $name
     * @return mixed
     */
    public function to($address, $name = null)
    {
        $this->to[$address] = $name;

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
     * @param $view
     * @param $parameters
     */
    public function view($view, $parameters)
    {
        $this->view = view($view, $parameters)->render();

        return $this;
    }

    /**
     * Send the message
     *
     * @param $sendable
     * @return int
     */
    public function send($sendable = null)
    {
        if ($sendable)
        {
            $sendable->build();

            return $this->prepare($sendable);
        }

        return $this->prepare($this);
    }

    protected function prepare($mail)
    {
        // Create the Transport
        $mailer = new Swift_Mailer($this->getTransport());

        // Create a message
        $message = (new Swift_Message($mail->subject))
            ->setFrom($mail->from ? $mail->from : config('mail.from'))
            ->setTo($mail->to ? $mail->to : $this->to);

        if ($mail->body)
        {
            $message->setBody($mail->body, 'text/plain');
        }

        if ($mail->view)
        {
            $message->setBody($mail->view, 'text/html');
        }

        // Send the message
        $result = $mailer->send($message);

        return $result;
    }

    /**
     * Get transport
     *
     * @return mixed
     */
    public function getTransport()
    {
        return Transport::getInstance()->getTransport();
    }
}