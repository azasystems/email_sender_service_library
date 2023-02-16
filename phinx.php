<?php

use Dotenv\Dotenv;

use function AzaSystems\Helpers\env;
use function AzaSystems\Helpers\envLoad;

require_once __DIR__ . '/src/helpers/common.php';

// Считывание .env для функции env()
envLoad();
// die(env('DB_PORT', '5432'));

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
//        'production' => [
//            'adapter' => 'mysql',
//            'host' => 'localhost',
//            'name' => 'production_db',
//            'user' => 'root',
//            'pass' => '',
//            'port' => '3306',
//            'charset' => 'utf8',
//        ],
        'development' => [
            'adapter' => 'pgsql',
            'host' => env('DB_HOST', 'localhost'),
            'name' => env('DB_DATABASE', 'email_sender_service'),
            'user' => env('DB_USERNAME', 'postgres'),
            'pass' => env('DB_PASSWORD', 'postgres1'),
            'port' => env('DB_PORT', '5432'),
            'charset' => 'utf8',
        ],
//        'testing' => [
//            'adapter' => 'mysql',
//            'host' => 'localhost',
//            'name' => 'testing_db',
//            'user' => 'root',
//            'pass' => '',
//            'port' => '3306',
//            'charset' => 'utf8',
//        ]
    ],
    'version_order' => 'creation'
];
