<?php

namespace Roster\Http\Testing;

use PHPUnit\Framework\TestCase;

class Response extends TestCase
{
    protected $headers = [];
    
    public function get($route)
    {
        $this->headers = get_headers($route);

        return $this;
    }

    public function assertStatus($code)
    {
        return $this->assertEquals(substr($this->headers[0], 9, 3), $code);
    }
}
