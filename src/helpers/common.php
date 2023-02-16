<?php

declare(strict_types=1);

namespace AzaSystems\Helpers;

// Считывание .env для функции env()
use Dotenv\Dotenv;

function envLoad(): void
{
    $dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../../');
    $dotenv->load();
}

/*
 * Получить переменные среды
 * */
function env(string $name, string $default = ''): mixed
{
    if ($name === 'DB_HOST' && empty($_SERVER['DOCUMENT_ROOT'])) {
        // Для докера надо имя Postgres контейнера, а для шелла надо localhost для разработки, или адрес сервера для продакшена
        // # For docker Instead of localhost or 127.0.0.1 I must use the container name. Now I remember. - https://forums.docker.com/t/cant-get-postgres-to-work/29580/3
        return getenv('DB_HOST_SHELL') ?: $_ENV['DB_HOST_SHELL'] ?? $default;
    }

    return getenv($name) ?: $_ENV[$name] ?? $default;
}

function envInt(string $name, int $default = 0): int
{
    return (int)env($name, (string)$default);
}

/**
 * Get options from the command line or web request
 * https://www.php.net/manual/ru/function.getopt.php
 */
function getOptions(string $options, array $longOptions): bool|array
{
    if (PHP_SAPI === 'cli' || empty($_SERVER['REMOTE_ADDR'])) {  // command line
        return getopt($options, $longOptions);
    } else { // if (isset($_REQUEST))  // web script
        $found = array();

        $shortOptions = preg_split('@([a-z\d]:{0,2})@i', $options, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $opts = array_merge($shortOptions, $longOptions);

        foreach ($opts as $opt) {
            if (str_ends_with($opt, '::')) {  // optional
                $key = substr($opt, 0, -2);

                if (isset($_REQUEST[$key]) && !empty($_REQUEST[$key])) {
                    $found[$key] = $_REQUEST[$key];
                } elseif (isset($_REQUEST[$key])) {
                    $found[$key] = false;
                }
            } elseif (str_ends_with($opt, ':')) {  // required value
                $key = substr($opt, 0, -1);

                if (isset($_REQUEST[$key]) && !empty($_REQUEST[$key])) {
                    $found[$key] = $_REQUEST[$key];
                }
            } elseif (ctype_alnum($opt)) {  // no value
                if (isset($_REQUEST[$opt])) {
                    $found[$opt] = false;
                }
            }
        }

        return $found;
    }
}
