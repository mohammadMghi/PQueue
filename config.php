<?php

return [
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'dbname' => getenv('DB_NAME') ?: 'test',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '852456',
    ],
    'queue' => [
        'default_queue' => 'default',
        'max_attempts' => 3,
        'retry_delay' => 1, // seconds
    ],
];

