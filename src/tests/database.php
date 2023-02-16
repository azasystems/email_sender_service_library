<?php

declare(strict_types=1);

namespace AzaSystems\Tests;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Service/logger.php';

use PDOException;

use function AzaSystems\App\Service\logger;
use function AzaSystems\Config\getDBConnection;

/**
 * Тестирование соединения к БД: возвращает успех или неудачно
 */
function testDBConnection(bool $debug = false): string
{
    try {
        $db = getDBConnection();
        $cnt = 0;
        foreach ($db->query('SELECT action_id from operations.actions') as $row) {
            if ($debug) {
                logger('');
                logger($row);
            }
            $cnt++;
        }
        $db = null;
        if ($cnt > 0) {
            return 'Соединение c БД выполнено успешно.';
        }
    } catch (PDOException $e) {
        logger("Error!: " . $e->getMessage());
        die();
    }

    return '';
}
