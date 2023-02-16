<?php

declare(strict_types=1);

namespace AzaSystems\Tests;

use function AzaSystems\App\Service\logger;
use function AzaSystems\Database\testFillEmails;
use function AzaSystems\Database\testFillSubscriptions;
use function AzaSystems\Database\testFillUsers;
use function AzaSystems\Database\truncateObjects;
use function AzaSystems\Database\truncateOperations;

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../../src/database/migrate.php';
require_once __DIR__ . '/../../src/app/Service/logger.php';

/**
 * Заполнение объектов базы до тестирования
 */
function runTester(int $count = 1000000): void
{
    logger(testDBConnection());
    logger(truncateObjects());
    logger("Заполнение 3-х объектов базы в размере $count записей (длительная операция, подождите несколько минут):");
    logger(testFillUsers($count));
    logger(testFillEmails($count));
    logger(testFillSubscriptions($count));
}

/**
 * Очищение объектов базы после тестирования
 */
function runTruncateAll(): void
{
    logger('Очищение объектов базы после тестирования:');
    logger(truncateObjects());
    logger(truncateOperations());
}
