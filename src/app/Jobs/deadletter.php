<?php

declare(strict_types=1);

/*
 * Скрипт обработки неотправленных писем по любой причине dead letter
 * */

namespace AzaSystems\Jobs\DeadLetter;

use function AzaSystems\App\Repository\DeadLetters\recoverDeadLetter;
use function AzaSystems\App\Service\logger;

require_once __DIR__ . '/../../app/Repository/deadletter.php';
require_once __DIR__ . '/../../app/Service/logger.php';

/**
 * Запустить восстановление dead letter
 */
function runDeadLetter(): void
{
    $count = recoverDeadLetter();
    logger("Восстановлено $count dead letter");
}
