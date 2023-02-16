<?php

declare(strict_types=1);

namespace AzaSystems\UI;

use function AzaSystems\App\Service\logger;

require_once __DIR__ . '/../app/Service/logger.php';

const SERVICE_NAME = "Сервис для рассылки почтовых уведомлений об истекающих подписках пользователей!";

/**
 * Описание сервиса
 */
function about(): void
{
    logger(SERVICE_NAME);
    logger("Вызов помощи:");
    logger("Для сайта: <a href='?help'>Помощь</a>");
    logger("Для шелл-команды: script.php?help");
}

/**
 * Помощь с командами сервиса
 */
function help(): void
{
    logger(SERVICE_NAME);
    logger("Вызов на сайте: script.php [parameters1]");
    logger("Вызов шелл-команды: script.php?parameters2");
    logger(
        "Где возможные значения [parameters1]: --help --service=tester --service=validator --service=sender --service=deadletter --service=truncate"
    );
    logger(
        "Где возможные значения [parameters2]: ?help&service=tester&service=validator&service=sender&service=deadletter&service=truncate"
    );
}
