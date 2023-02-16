<?php

declare(strict_types=1);

/*
 * Логирование данных
 */

namespace AzaSystems\App\Service;

use Idearia\FileLogger;

use function PHPUnit\Framework\isEmpty;

require_once __DIR__ . '/../../../src/app/View/emailTemplate.php';
require_once __DIR__ . '/FileLogger.php';

function logger(string $message, bool $error = false): string
{
    // 1) Отображение в командной строке
    // 2) Запись в файл
    FileLogger::$log_dir   = __DIR__ . '/../../../logs';
    FileLogger::$log_level = 'debug';
    FileLogger::$write_log = true;
    FileLogger::$print_log = true;
    if ($error) {
        $log_entry = FileLogger::error($message);
    } else {
        $log_entry = FileLogger::info($message);
    }

    $output_line = FileLogger::format_log_entry($log_entry);

    // 3) Отображение в браузере для веб-сервера
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        echo $output_line . '</br>';
    }

    return $output_line;
}
