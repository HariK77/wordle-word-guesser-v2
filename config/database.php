<?php
/**
 * Database Configuration (for future use)
 */

return [
    'default' => 'sqlite',

    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => dirname(dirname(__FILE__)) . '/storage/database.sqlite',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => getenv('DB_HOST') ?? 'localhost',
            'port' => getenv('DB_PORT') ?? 3306,
            'database' => getenv('DB_NAME') ?? 'word_guesser',
            'username' => getenv('DB_USER') ?? 'root',
            'password' => getenv('DB_PASS') ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
    ],
];
