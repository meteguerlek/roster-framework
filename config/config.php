<?php

return [
    'connection' => [
        'db_host' => grl('DB_HOST'),
        'db_user' => grl('DB_USERNAME'),
        'db_password' => grl('DB_PASSWORD'),
        'db_name' => grl('DB_DATABASE')
    ],
    'mail' => [
        'driver' => grl('MAIL_DRIVER'),
        'host' => grl('MAIL_HOST'),
        'port' => grl('MAIL_PORT'),
        'username' => grl('MAIL_USERNAME'),
        'password' => grl('MAIL_PASSWORD')
    ]
];