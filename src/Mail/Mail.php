<?php

namespace Roster\Mail;

class Mail
{
    public static function __callStatic($name, $arguments)
    {
        return (new Mailable)->{$name}(...$arguments);
    }
}
