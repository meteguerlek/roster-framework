<?php

namespace Roster\Auth;

use Roster\Support\Str;

class Generate
{
    /**
     * Generate token
     *
     * @param int $max
     * @return string
     */
    public static function token($max = 25)
    {
        return Str::rand($max);
    }
}
