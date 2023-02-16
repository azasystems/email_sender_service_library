<?php

declare(strict_types=1);

/*
 * Роутер маршрутов и функций
 */

namespace AzaSystems\App\Router;

use function AzaSystems\App\Service\logger;
use function AzaSystems\Jobs\DeadLetter\runDeadLetter;
use function AzaSystems\Jobs\Sender\runSender;
use function AzaSystems\Jobs\Validator\runValidator;
use function AzaSystems\Tests\runTester;
use function AzaSystems\Tests\runTruncateAll;
use function AzaSystems\UI\help;

require_once __DIR__ . '/../../helpers/common.php';
require_once __DIR__ . '/../../tests/index.php';
require_once __DIR__ . '/../../app/Jobs/validator.php';
require_once __DIR__ . '/../../app/Jobs/sender.php';
require_once __DIR__ . '/../../app/Service/logger.php';
require_once __DIR__ . '/../../UI/index.php';

/**
 * Роутер - обработка ключей и вызов нужных функций
 */
function runRouter(int|string $key, array $value): void
{
    if ($key === 'help') {
        help();
    }

    if ($key === 'service') {
        foreach ($value as $key => $service) {
            switch ($service) {
                case 'tester':
                    logger('Обработка аргумента tester');
                    runTester();

                    break;
                case 'truncate':
                    logger('Обработка аргумента truncate');
                    runTruncateAll();

                    break;
                case 'validator':
                    logger('Обработка аргумента validator');
                    runValidator();

                    break;
                case 'sender':
                    logger('Обработка аргумента sender');
                    runSender();

                    break;
                case 'deadletter':
                    logger('Обработка аргумента deadletter');
                    runDeadletter();

                    break;
            }
        }
    }
}
