<?php

namespace App\Http\Middleware;

use Roster\Http\Request;

class TrimStrings
{
    protected $except = [
        'password',
        'password_confirmation'
    ];

    public function handle($request)
    {
        if (!Request::isPost())
        {
            return true;
        }

        foreach ($request->all() as $key => $value)
        {
            if (!in_array($key, $this->except))
            {
                if (is_string($request->{$key}))
                {
                    $request->{$key} = trim($request->{$key});
                }
            }
        }
    }
}
