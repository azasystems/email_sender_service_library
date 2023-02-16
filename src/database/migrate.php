<?php

declare(strict_types=1);

namespace AzaSystems\Database;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Service/logger.php';

use PDOException;

use function AzaSystems\App\Service\logger;
use function AzaSystems\Config\getDBConnection;

const PROC = [
    'users'         => 'seed_test_users',
    'emails'        => 'seed_test_emails',
    'subscriptions' => 'seed_test_subscriptions',
];

const TABLE = [
    'users'         => 'Пользователи',
    'emails'        => 'Электронные почты',
    'subscriptions' => 'Подписки',
];

function callProc(string $procName, string $tableName, int $usersNumber): string
{
    $query = "call objects." . $procName . "($usersNumber)";
    try {
        $db = getDBConnection();
        $db->query($query);
        $db = null;

        return "Таблица $tableName заполнена тестовыми данными в количестве $usersNumber.";
    } catch (PDOException $e) {
        logger("Error!: " . $e->getMessage());
        die();
    }
}

/**
 * Заполнение таблицы пользователей тестовыми данными
 */
function testFillUsers(int $usersNumber): string
{
    return callProc(PROC['users'], TABLE['users'], $usersNumber);
}

/**
 * Заполнение таблицы электронных почт тестовыми данными
 */
function testFillEmails(int $emailsNumber): string
{
    return callProc(PROC['emails'], TABLE['emails'], $emailsNumber);
}

/**
 * Заполнение таблицы подписок тестовыми данными
 */
function testFillSubscriptions(int $subscriptionsNumber): string
{
    return callProc(PROC['subscriptions'], TABLE['subscriptions'], $subscriptionsNumber);
}

/**
 * Удаление данных в схеме objects в БД
 */
function truncateObjects(): string
{
    $query = "
    SET search_path TO objects;
    truncate table subscriptions, emails, users RESTART IDENTITY CASCADE;    
    ";
    try {
        $db = getDBConnection();
        $db->query($query);
        $db = null;

        return "Таблицы схемы objects почищены.";
    } catch (PDOException $e) {
        logger("Error!: " . $e->getMessage());
        die();
    }
}

/**
 * Удаление данных в схеме operations в БД
 */
function truncateOperations(): string
{
    $query = "
    SET search_path TO operations;
    truncate table validator, sender, deadletter RESTART IDENTITY CASCADE;    
    ";
    try {
        $db = getDBConnection();
        $db->query($query);
        $db = null;

        return "Таблицы схемы operations почищены.";
    } catch (PDOException $e) {
        logger("Error!: " . $e->getMessage());
        die();
    }
}
