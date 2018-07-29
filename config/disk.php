<?php
return [
    'view' => 'resources.views',
    'controllers' => 'app.Http.Controllers',
    'models' => 'app',
    'middleware' => 'app.Http.Middleware',
    'customView' => 'src.View.views',
    'storage' => [
        'view' => 'storage.app.views',
        'files' => 'public.files',
        'logs' => 'storage.app.logs',
        'cache' => 'storage.framework.cache'
    ],
    'lang' => 'resources.lang'
];