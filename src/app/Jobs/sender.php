<?php

declare(strict_types=1);

/*
 * Скрипт отправки email истекающих подписок
 * Примерно за три дня до истечения срока подписки,
 * нужно отправить письмо пользователю с текстом "{username}, your subscription is expiring soon".
 *
 * А перед этим за 7 дней скриптом validator проверяются почты на валидность (и это платная проверка)
 * Однако, тут надо вначале перед отправкой снова проверить, есть ли еще такие не проверенные почты ?
 * */

namespace AzaSystems\Jobs\Sender;

use Exception;

use function AzaSystems\App\Repository\Validator\getEmailsToValidate;
use function AzaSystems\App\Repository\Validator\getValidSubscriptions;
use function AzaSystems\App\Service\insertSubscriptionsToSenderByChunks;
use function AzaSystems\App\Service\sendEmailsFromSender;
use function AzaSystems\App\Service\validateEmailsByChunks;
use function AzaSystems\Helpers\env;
use function AzaSystems\Helpers\envInt;
use function AzaSystems\Jobs\DeadLetter\runDeadLetter;

require_once __DIR__ . '/../../app/Repository/sender.php';
require_once __DIR__ . '/../../app/Repository/validator.php';
require_once __DIR__ . '/../../app/Service/sender.php';
require_once __DIR__ . '/../../app/Service/logger.php';
require_once __DIR__ . '/../../app/Jobs/deadletter.php';

/**
 * Запустить отправку писем
 * @throws Exception
 */
function runSender(): void
{
    // Особый случай: устаревшие валидные почты
    // Этим занимается только валидатор заранее, смотри runValidator()

    // Особый случай: запуск восстановления почт, ранее не отправленных по любой причине
    runDeadLetter();

    // Выборка почт истекающих подписок за 3 дня,
    // и плюс у кого почта по любой причине не прошла валидацию из репозитория неделю назад
    $statement = getEmailsToValidate(envInt('CRON_SEND_BEFORE_EXPIRE', 3));

    // Проверка пачками не проверенных почт в сервисе Validator
    // Это делаем непосредственно перед отправкой.
    // Такой случай не должен происходить, но может, если валидация неделю назад по любой причине не произошла.
    validateEmailsByChunks($statement);
    $statement = null;

    // Получить все валидные подписки за CRON_SEND_BEFORE_EXPIRE дней до истечения подписки,
    // Выборка почт истекающих подписок за 3 дня,
    // и плюс у кого почта прошла валидацию из репозитория или неделю назад, или сейчас выше в validateEmailsByChunks()
    // false тут дает нам возможность взять только валидные почты и отправить по ним письмо
    $statement = getValidSubscriptions(envInt('CRON_SEND_BEFORE_EXPIRE', 3));

    // Отправка в очередь таблицы Sender пачками подписки в сервисе Sender
    insertSubscriptionsToSenderByChunks($statement);
    $statement = null;

    // Отправка подписок из очереди таблицы Sender в виде писем
    // Это вообще можно вынести в отдельный крон, который раз в час запускается
    sendEmailsFromSender((string)env('EMAIL_FROM', "alert@alert.com"));
}
