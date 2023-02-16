<?php

declare(strict_types=1);

/*
 * Репозиторий валидации электронных почт
 */

namespace AzaSystems\App\Repository\Validator;

use Exception;
use PDO;
use PDOStatement;

use function AzaSystems\App\Service\logger;
use function AzaSystems\Config\getDBConnection;

use const AzaSystems\App\Model\EMAIL_IS_VALID;
use const AzaSystems\App\Model\EMAIL_IS_EXPIRED;
use const AzaSystems\App\Model\EMAIL_NOT_VALID;

require_once __DIR__ . '/../../../src/app/Service/validator.php';
require_once __DIR__ . '/../../../src/app/Service/logger.php';
require_once __DIR__ . '/../../config/database.php';

/**
 * Особый случай: устаревшие валидные почты
 * Если почта валидна, но была проверена ранее подольше чем 180 дней (VALIDATE_EXPIRE в днях - настроить в конфиге),
 * то надо бы снова сделать валидацию почты перед отправкой.
 */
function updateExpiredValidator(int $validateExpireDays): int
{
    $db = getDBConnection();

    // Тут вопрос: надо ли включать сюда еще и EMAIL_NOT_VALID, чтобы их через полгода бы дополнительно проверить ?
    // Пока решил, что не надо.
    $actionIds = (string)EMAIL_IS_VALID;

    $query = "
    UPDATE operations.validator
    SET action_id = ".EMAIL_IS_EXPIRED."
    WHERE action_id IN ($actionIds) AND updated_at < (NOW() - $validateExpireDays * (interval '1' DAY))
";

    $stmt = $db->prepare($query);
    $stmt->execute();

    // Число обновленных строк
    return $stmt->rowCount();
}

/**
 * 1) Получить все email истекающих подписок за $beforeDays дней до истечения подписки,
 * то есть за $beforeDays + CRON_SEND_BEFORE_EXPIRE дней до отправки писем
 * 2) Кроме подписок с уже отправленными письмами: NOT se.send_at ISNULL
 * @throws Exception
 */
function getEmailsToValidate(int $beforeDays, bool $excludeValidEmails = true): PDOStatement
{
    $db = getDBConnection();

    // 1 сейчас - NOW()
    // 2 день 1
    // 3 день 2
    // 4 день 3 - истечение - validts

    // Шаг 1 - получить все истекающие подписки subscriptions по полю validts
    // Шаг 2 - получить все почты emails у этих истекающих подписок subscriptions по полю email_id

    if ($excludeValidEmails) {
        // Шаг 3 - исключить почты, которые уже были проверены и у которых финальные action_id IN (2, 3)
        //         Это Join Where Not Exists - https://stackoverflow.com/questions/750343/mysql-join-where-not-exists
        //         AND v.email_id ISNULL => А без B => https://i.pinimg.com/originals/b3/80/4b/b3804b751f134180015b4e6583080246.png
        // v.action_id IN (EMAIL_NOT_VALID=2, EMAIL_IS_VALID=3) => 2 и 3 они уже финальные действия.
        // При этом EMAIL_IS_EXPIRED=4 мы возвращаем: их же надо повторно валидировать
        $actionIds = EMAIL_NOT_VALID . ", " . EMAIL_IS_VALID;
        // Дополнительное условие, чтобы A без B отфильтровать
        $excludeEmails = "AND v.email_id ISNULL";
        $select = "e.email_id, e.email";
    } else {
        // Шаг 3 - включить почты, которые уже валидные с action_id = 3
        $actionIds     = EMAIL_IS_VALID;
        $excludeEmails = "";
        $select = "e.email_id, e.email, s.subscription_id";
    }

    $query = "
SELECT $select
FROM objects.subscriptions s
JOIN objects.emails e on e.email_id = s.email_id
LEFT JOIN operations.validator v on v.email_id = s.email_id and v.action_id 
    IN ($actionIds)
LEFT JOIN operations.sender se on se.subscription_id = s.subscription_id
WHERE (NOW() + $beforeDays * (interval '1' DAY)) > s.validts
    $excludeEmails AND se.send_at ISNULL
ORDER BY e.email_id
        ";

    $statement = $db->prepare($query, [PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL]);
    // Проверка пачками не проверенных почт в сервисе Validator
    if ($statement === false) {
        throw new Exception(logger("getEmailsToValidate имеет на выходе false", true));
    }

    $statement->execute();
    return $statement;
}

/**
 * Получить все подписки за $beforeDays дней до истечения подписки,
 * то есть за $beforeDays + CRON_SEND_BEFORE_EXPIRE дней до отправки писем
 * @throws Exception
 */
function getValidSubscriptions(int $beforeDays): PDOStatement
{
    // Не стал делать отдельный запрос, а в этом же возьму дополнительное поле s.subscription_id
    return getEmailsToValidate($beforeDays, false);
}

/**
 * Вставить или обновить "Текущее действие по проверке и валидации почты" (validator.action_id)
 * https://stackoverflow.com/questions/1109061/insert-on-duplicate-update-in-postgresql
 */
function updateValidatorEmailAction(int $emailId, int $actionId): void
{
    logger("set validator.action_id($emailId, $actionId)");
    $db = getDBConnection();

    // Обновляем updated_at для контроля истечения срока валидации, по умолчанию через VALIDATE_EXPIRE=180 дней
    $query = $db->prepare(
        "
INSERT INTO operations.validator (email_id, action_id) 
VALUES (:email_id, :action_id)
ON CONFLICT (email_id) DO UPDATE 
    SET action_id  = excluded.action_id,
        updated_at = NOW()
"
    );
    $query->bindValue(':email_id', $emailId, PDO::PARAM_INT);
    $query->bindValue(':action_id', $actionId, PDO::PARAM_INT);
    $query->execute();
}
