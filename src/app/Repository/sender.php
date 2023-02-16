<?php

declare(strict_types=1);

/*
 * Репозиторий отправки электронных почт
 */

namespace AzaSystems\App\Repository\Sender;

use Exception;
use PDO;

use PDOStatement;

use function AzaSystems\App\Service\logger;
use function AzaSystems\Config\getDBConnection;

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Service/logger.php';

/*
 * Примерно за три дня до истечения срока подписки,
 * нужно отправить письмо пользователю с текстом "{username}, your subscription is expiring soon".
 */
function insertSender(array $subscriptions): int
{
    $val = [];
    foreach ($subscriptions as $subscription) {
        $val[] = '(' . $subscription['subscription_id'] . ')';
    }
    $query = 'INSERT INTO operations.sender(subscription_id) 
              VALUES '.implode(',', $val) .
             '  ON CONFLICT (subscription_id) 
                    DO UPDATE SET send_at = NULL';

    $statement = getDBConnection()->prepare($query);
    $statement->execute();

    return $statement->rowCount();
}

/**
 * Получить данные для отправки
 * @throws Exception
 */
function getDataToSend(): PDOStatement
{
    $db = getDBConnection();

    $query = "
SELECT se.sender_id, su. subscription_id, e.email, u.user_name
FROM operations.sender se
JOIN objects.subscriptions su on su.subscription_id = se.subscription_id
JOIN objects.emails e on e.email_id = su.email_id
JOIN objects.users u on u.user_id = e.user_id
WHERE se.send_at ISNULL
ORDER BY se.sender_id
        ";

    $statement = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
    if ($statement === false) {
        throw new Exception(logger("getDataToSend имеет на выходе false", true));
    }

    $statement->execute();

    return $statement;
}

/**
 * Обновляем поле send_at, если успешная отправка
 */
function updateSendAt(array $results): void
{
    $query = "
UPDATE operations.sender 
SET send_at = NOW() 
WHERE sender_id IN (".implode(',', $results).")";

    $statement = getDBConnection()->prepare($query);
    $statement->execute();
}
