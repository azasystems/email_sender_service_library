<?php

declare(strict_types=1);

/*
 * Сервис отправки email истекающих подписок
 */

namespace AzaSystems\App\Service;

use Exception;
use PDO;
use PDOStatement;
use React\Promise\Promise;

use Throwable;

use function AzaSystems\App\Repository\DeadLetters\deleteSuccessDeadLetters;
use function AzaSystems\App\Repository\DeadLetters\insertDeadLetter;
use function AzaSystems\App\Repository\Sender\getDataToSend;
use function AzaSystems\App\Repository\Sender\insertSender;
use function AzaSystems\App\Repository\Sender\updateSendAt;
use function AzaSystems\App\View\getEmailTemplate;
use function AzaSystems\Helpers\env;

use function AzaSystems\Helpers\envInt;
use function React\Async\parallel;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/app/Model/actions.php';
require_once __DIR__ . '/../../../src/app/Repository/sender.php';
require_once __DIR__ . '/../../../src/app/Repository/deadletter.php';
require_once __DIR__ . '/../../../src/app/View/emailTemplate.php';
require_once __DIR__ . '/../../../src/app/Service/logger.php';

/*
 * Отсылает емейл. Функция работает от 1 секунды до 10 секунд.
 * */
function send_email(
    string $email,
    string $from,
    string $to,
    string $subj,
    string $body
): void {
    logger(sprintf('Отсылаем почту в send_email(): %s;%s;%s;%s;%s', $email, $from, $to, $subj, $body));

    // Здесь мы проверяем отправку deadletter
    if (env('APP_ENV') === 'local') {
        try {
            if (random_int(1, 2) === 2) {
                throw new Exception('test deadletter ');
            }
        } catch (Exception) {
        }
    }

    try {
        $delay = envInt('SEND_EMAIL_DELAY', 10);
        sleep(random_int(1, $delay));
    } catch (Exception) {
    }
}

/*
 * Отправка в очередь пакетов электронных почт по CHUNK (из конфига) штук
 * */
function insertSubscriptionsToSenderByChunks(PDOStatement $stmt): void
{
    $chunk = envInt('CHUNK', 10);
    logger("Отправка в очередь пакетов электронных почт по $chunk штук.");

    $cnt  = 0;
    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $row;
        if (count($data) >= $chunk) {
            list($cnt, $data) = insertEmailsWhile($cnt, $data);
        }
    }
    if ($data !== []) {
        list($cnt, ) = insertEmailsWhile($cnt, $data);
    }
    logger("Отправка в очередь пакетов электронных почт, обработано $cnt пакетов.");
}

/*
 * Обработка пакета номер count
 * */
function insertEmailsWhile(int $count, array $data): array
{
    $count++;
    logger("Обработка пакета номер $count");

    $countInserted = insertSender($data);
    logger("Вставлено записей в таблицу sender: $countInserted");
    //sleep(60);

    return array($count, []);
}

/**
 * Обработка очереди таблицы Sender пакетами и отправка их почты, путем параллельного вызова отправки почты
 * Мы берем из очереди пользователей и отправляем письма пока пользователи в очереди не закончатся
 * @throws Exception
 */
function sendEmailsFromSender(string $emailFrom): void
{
    $statement = getDataToSend();
    sendEmailsByChunks($statement, $emailFrom);
}

/*
 * Отправка пакетами писем по CHUNK (из конфига) штук
 * */
function sendEmailsByChunks(PDOStatement $stmt, string $emailFrom): void
{
    $chunk = envInt('CHUNK', 10);
    logger("Отправка пакетами писем по $chunk штук.");

    $count = 0;
    $data  = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $row;
        if (count($data) >= $chunk) {
            list($count, $data) = sendEmailsWhile($count, $data, $emailFrom);
        }
    }
    if ($data !== []) {
        list($count, ) = sendEmailsWhile($count, $data, $emailFrom);
    }
    logger("Отправка пакетами писем завершена, обработано $count пакетов.");
}

/*
 * Отправка пакета писем номер count
 * */
function sendEmailsWhile(int $count, array $data, string $emailFrom): array
{
    $count++;
    logger("Отправка пакета писем номер $count");
    sendEmailsParallel($data, $emailFrom);
    //sleep(60);

    return array($count, []);
}

/**
 * Отправка почты пачками одновременно из очереди таблицы Sender
 * Это делается через Async и Promise утилиты от библиотеки react/async: https://reactphp.org/async/
 */
function sendEmailsParallel(array $rows, string $emailFrom): void
{
    $tasks = [];
    foreach ($rows as $row) {
        $tasks[] = function () use ($emailFrom, $row): Promise {
            return new Promise(function (callable $resolve) use ($emailFrom, $row) {
                try {
                    list($subj, $msg) = getEmailTemplate($row['user_name'], $row['subscription_id']);

                    send_email($row['email'], $emailFrom, $row['email'], $subj, $msg);
                } catch (Throwable $e) {
                    logger('Ошибка при отправке почты: ' . $row['email'] . ': ' . $e->getMessage(), true);

                    // Тут надо в deadletter вставить
                    insertDeadLetter($row['subscription_id']);
                }

                $resolve($row['sender_id']);
            });
        };
    }

    parallel($tasks)
        ->then(function (array $results) {
            updateSendAt($results);
            logger('Обновили успешно send_at в таблице Sender при parallel().');
            $countDead = deleteSuccessDeadLetters($results);
            if ($countDead > 0) {
                logger("Удалили $countDead записей в таблице deadletter при parallel().");
            }
        }, function (Exception $e) {
            logger('Ошибка sendEmailsParallel при parallel(): ' . $e->getMessage(), true);
        });
}
