<?php

declare(strict_types=1);

/*
 * Скрипт валидации email истекающих подписок
 * За 7 дней до отправки писем, скриптом validator заранее проверяются почты на валидность (и это платная проверка)
 */

namespace AzaSystems\Jobs\Validator;

use Exception;

use function AzaSystems\App\Repository\Validator\updateExpiredValidator;
use function AzaSystems\App\Repository\Validator\getEmailsToValidate;
use function AzaSystems\App\Service\logger;
use function AzaSystems\App\Service\validateEmailsByChunks;
use function AzaSystems\Helpers\env;
use function AzaSystems\Helpers\envInt;

require_once __DIR__ . '/../../app/Repository/validator.php';
require_once __DIR__ . '/../../app/Service/logger.php';

/**
 * Запустить валидацию электронных почт
 * @throws Exception
 */
function runValidator(): void
{
    // Особый случай: устаревшие валидные почты
    // Если почта валидна, но была проверена ранее подольше чем 180 дней (VALIDATE_EXPIRE в днях - настроить в конфиге),
    // то надо бы снова сделать валидацию почты перед отправкой, для этого выполняем сброс в EMAIL_IS_EXPIRED=4
    logger('Выполняем обновление истекших валидаций почт:');
    $validateExpireDays = (int)env('VALIDATE_EXPIRE', "180");
    $count = updateExpiredValidator($validateExpireDays);
    logger("Сбросили валидации у $count устаревших валидных почт с валидацией старее $validateExpireDays дней.");

    // Выборка почт истекающих подписок из репозитория
    //  Получить все email истекающих подписок за CRON_VALIDATE_BEFORE_SEND + CRON_SEND_BEFORE_EXPIRE дней до истечения подписки,
    // * то есть за CRON_VALIDATE_BEFORE_SEND дней до отправки писем
    $statement = getEmailsToValidate(
        envInt('CRON_VALIDATE_BEFORE_SEND', 7) +
        envInt('CRON_SEND_BEFORE_EXPIRE', 3)
    );

    validateEmailsByChunks($statement);
    $statement = null;
}
