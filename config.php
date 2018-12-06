<?php


return [
    'database' => [
        'name' => 'dbname',
        'username' => 'root',
        'password' => '',
        'connection' => 'mysql:host=127.0.0.1',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    ],
    'session' => [
        'token_name' => 'token',
        'session_name' => 'user'
    ],
    'remember' => [
        'cookie_name' => 'user',
        'cookie_expire' => 604800
    ]
];
