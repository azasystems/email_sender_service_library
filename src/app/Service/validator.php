<?php

declare(strict_types=1);

/*
 * Сервис для валидации электронных почт
 */

namespace AzaSystems\App\Service;

/*
 * Сервис валидации email истекающих подписок
 * Регулярность: crontab, запуск 1 раз в 12 часов - число 12 настраивается в конфиге crontab, рекомендуется ставить от 1 до 12.
 * Нам не нужно делать валидацию почту сразу после регистрации пользователя и подтверждения почты,
 * потому что почты могут со временем и отваливаться из жизни,
 * то есть почтовые ящики могут не работать, пользователи или сами сервера почты могут блокироваться,
 * поэтому почты надо проверять за неделю до самой отправки.
 * Несколько дней (неделю) надо, потому что надо успеть у всех почт сделать валидацию до отправки,
 * а в крайнем тестовом случае могут же все 1 М почт потребоваться к валидации сразу,
 * да еще и сервис валидации может на сутки-двое прилечь, это тоже возможно!
 * Массовая валидация email истекающих подписок - делается этим скриптом массово,
 * за CRON_VALIDATE_BEFORE_SEND + CRON_SEND_BEFORE_EXPIRE дней до истечения подписки,
 * то есть за CRON_VALIDATE_BEFORE_SEND дня до отправки писем.
 * Если почта валидна, но была проверена дольше чем 180 дней (VALIDATE_EXPIRE в днях - настроить в конфиге),
 * то надо снова сделать валидацию перед отправкой.
 * Если почта не проверена (например дольше суток работает эта массовая валидация - если например надо все 1 млн проверить),
 * то ящик почты проверяется перед отправкой письма.
 * Считывание и отправка пачками, а не сразу 1 миллион (в худшем случае) считывать в память, по 100-500 записей (вынести в конфиг)
 */

use Exception;
use PDO;
use PDOStatement;
use React\Promise\Promise;

use Throwable;

use function AzaSystems\App\Repository\Validator\updateValidatorEmailAction;

use function AzaSystems\Helpers\envInt;
use function React\Async\parallel;

use const AzaSystems\App\Model\EMAIL_IS_VALID;
use const AzaSystems\App\Model\EMAIL_NOT_VALID;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/app/Model/actions.php';
require_once __DIR__ . '/../../../src/app/Repository/validator.php';
require_once __DIR__ . '/../../../src/app/Service/logger.php';
require_once __DIR__ . '/../../../src/helpers/common.php';

/*
 * Проверяет емейл на валидность и возвращает 0 или 1.
 * Функция работает от 1 секунды до 1 минуты (CHECK_EMAIL_DELAY в секундах).
 * Вызов функции платный (это значит надо как меньше стараться ее вызывать, то есть только при необходимости).
 * */
function check_email(string $email): bool
{
    logger("check_email($email)");

    $delay = envInt('CHECK_EMAIL_DELAY', 60);
    try {
        // Указав 0, не будет задержки (надо мне, для целей отладки большого числа данных)
        if ($delay > 1) {
            sleep(random_int(1, $delay));
        }
        $result = (bool)random_int(0, 1);
    } catch (Exception) {
        $result = true;
    }

    return $result;
}

/*
 * Обработка пакетами электронных почт по CHUNK (из конфига) штук
 * */
function validateEmailsByChunks(PDOStatement $stmt): void
{
    $chunk = envInt('CHUNK', 10);
    logger("Обработка пакетами электронных почт по $chunk штук.");

    $cnt   = 0;
    $data  = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $row;
        if (count($data) >= $chunk) {
            list($cnt, $data) = validateEmailsWhile($cnt, $data);
        }
    }
    if ($data !== []) {
        list($cnt, ) = validateEmailsWhile($cnt, $data);
    }
    logger("Обработка пакетами электронных почт завершена, обработано $cnt пакетов.");
}

function validateEmailsWhile(int $cnt, array $data): array
{
    $cnt++;

    logger("Обработка пакета номер $cnt");
    validateEmails($data);
    //sleep(60);

    return array($cnt, []);
}

/**
 * Проверки валидности почты пачками одновременно
 * Это делается через Async и Promise утилиты от библиотеки react/async: https://reactphp.org/async/
 */
function validateEmails(array $rows): void
{
    $tasks = [];
    foreach ($rows as $row) {
        $tasks[] = function () use ($row): Promise {
            return new Promise(function (callable $resolve) use ($row) {
                try {
                    // echo 'Мы внутри Promise';
                    $result = check_email($row['email']);
                    if ($result === true) {
                        // Валидный
                        updateValidatorEmailAction((int)$row['email_id'], EMAIL_IS_VALID);
                    } else {
                        // Не валидный
                        updateValidatorEmailAction((int)$row['email_id'], EMAIL_NOT_VALID);
                    }
                } catch (Throwable $e) {
                    logger('Ошибка при проверке почты: ' . $row['email'] . $e->getMessage(), true);
                }

                $resolve('ok для email_id = ' . $row['email_id']);
            });
        };
    }

    parallel($tasks)
        ->then(function (array $results) {
            foreach ($results as $result) {
                logger($result);
            }
        }, function (Exception $e) {
            logger('Ошибка validateEmails при parallel(): ' . $e->getMessage(), true);
        });
}
