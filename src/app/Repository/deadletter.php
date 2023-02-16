<?php


declare(strict_types=1);

/*
 * Репозиторий обработки неотправленных электронных почт
 */

namespace AzaSystems\App\Repository\DeadLetters;

use function AzaSystems\Config\getDBConnection;

require_once __DIR__ . '/../../config/database.php';

/*
 * Добавление неотправленной почты по данной подписке
 * */
function insertDeadLetter(int $subscriptionId): int
{
    $query = "INSERT INTO operations.deadletter(subscription_id) 
              VALUES ($subscriptionId) 
                    ON CONFLICT (subscription_id) 
                        DO UPDATE SET try_count = operations.deadletter.try_count + 1";

    $statement = getDBConnection()->prepare($query);
    $statement->execute();

    return $statement->rowCount();
}

/*
 * Восстановление неотправленной почты по данной подписке
 * */
function recoverDeadLetter(): int
{
    $query = "
UPDATE operations.sender s
SET    send_at = NULL
WHERE s.subscription_id IN (
    SELECT subscription_id
    FROM operations.deadletter
)
";

    $statement = getDBConnection()->prepare($query);
    $statement->execute();

    return $statement->rowCount();
}

/**
 * Обновляем, если была успешная отправка
 */
function deleteSuccessDeadLetters(array $senderIds): int
{
    $query = "
DELETE FROM operations.deadletter 
WHERE subscription_id IN (
    SELECT subscription_id 
    FROM operations.sender 
    WHERE sender_id IN (
        ".implode(',', $senderIds)."))";

    $stm = getDBConnection()->prepare($query);
    $stm->execute();

    return $stm->rowCount();
}
