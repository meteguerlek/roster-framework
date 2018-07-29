<?php

return [
    'debug' => grl('DEBUG'),

    'logger' => grl('LOGGER'),

    'app_url' => grl('APP_URL'),

    // Site language
    'locale' => 'en',

    // Class Aliases
    'aliases' => [
        'App' => Roster\Support\App::class,
        'Auth' => Roster\Auth\Roster\Auth::class,
        'Alert' => Roster\Support\Alert::class,
        'Carbon' => Carbon\Carbon::class,
        'Session' => Roster\Sessions\Session::class,
        'Route' => Roster\Routing\Router::class
    ],

    // every page reload forget this sessions
    'forgets' => [
        'errors',
        'successes',
        'inputs'
    ],
    'controllers' => 'App\\Http\\Controllers\\'
];

