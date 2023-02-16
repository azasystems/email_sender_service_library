<?php

declare(strict_types=1);

/*
 * Front Controller
 * Шаблон Front Controller для реализации рабочих процессов своего приложения.
 * Он имеет единую точку входа (script.php) для всех своих запросов.
 */
namespace AzaSystems;

use Dotenv\Dotenv;

use function AzaSystems\App\Router\runRouter;
use function AzaSystems\App\Service\logger;
use function AzaSystems\Helpers\envLoad;
use function AzaSystems\UI\about;
use function AzaSystems\Helpers\getOptions;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/helpers/common.php';
require_once __DIR__ . '/src/app/Router/router.php';
require_once __DIR__ . '/index.php';

// Считывание .env для функции env()
envLoad();

$args = getOptions('', ['help::', 'service::']);

if (count($args) === 0) {
    about();
    logger("Выход.");
    exit();
}

foreach ($args as $key => $value) {
    runRouter($key, (array)$value);
}
