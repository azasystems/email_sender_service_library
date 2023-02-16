<?php

declare(strict_types=1);

namespace AzaSystems\Config;

require_once __DIR__ . '/../../src/helpers/common.php';

use PDO;

use function AzaSystems\Helpers\env;
use function AzaSystems\Helpers\envLoad;

/**
 * Соединение с БД https://www.php.net/manual/ru/pdo.connections.php
 */
function getDBConnection(
    string $type = 'pgsql',
    string $format = "pgsql:host=%s;port=%s;dbname=%s"
): PDO {
    envLoad();

    $config = getDBConfig();
    $conn   = $config['connections'][$type];

    //var_dump($conn);
    return new PDO(
        sprintf(
            $format,
            $conn['host'],
            $conn['port'],
            $conn['database'],
        ),
        $conn['username'],
        $conn['password'],
        $conn['options']
    );
}

/**
 * Конфиг БД импортирован из laravel
 */
function getDBConfig(): array
{
    return [
        /*
        |--------------------------------------------------------------------------
        | PDO Fetch Style
        |--------------------------------------------------------------------------
        |
        | By default, database results will be returned as instances of the PHP
        | stdClass object; however, you may desire to retrieve records in an
        | array format for simplicity. Here you can tweak the fetch style.
        |
        */

        'fetch' => PDO::FETCH_ASSOC,

        /*
        |--------------------------------------------------------------------------
        | Default Database Connection Name
        |--------------------------------------------------------------------------
        |
        | Here you may specify which of the database connections below you wish
        | to use as your default connection for all database work. Of course
        | you may use many connections at once using the Database library.
        |
        */

        'default' => 'pgsql',

        /*
        |--------------------------------------------------------------------------
        | Database Connections
        |--------------------------------------------------------------------------
        |
        | Here are each of the database connections setup for your application.
        | Of course, examples of configuring each database platform that is
        | supported by Laravel is shown below to make development simple.
        |
        |
        | All database work in Laravel is done through the PHP PDO facilities
        | so make sure you have the driver for your particular database of
        | choice installed on your machine before you begin development.
        |
        */

        'connections' => [
            'pgsql' => [
                'host'     => env('DB_HOST', 'localhost'),
                'port'     => env('DB_PORT', '5432'),
                'database' => env('DB_DATABASE', 'email_sender_service'),
                'username' => env('DB_USERNAME', 'postgres'),
                'password' => env('DB_PASSWORD', 'postgres1'),
                'options'  => [
                    PDO::ATTR_EMULATE_PREPARES => true,
                    PDO::ATTR_PERSISTENT       => true, // https://www.php.net/manual/en/pdo.connections.php
                ],
            ],
        ],
    ];
}
