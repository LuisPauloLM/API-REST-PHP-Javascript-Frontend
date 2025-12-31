<?php

declare(strict_types=1);

class Logger
{
    private static string $LOG_FILE = __DIR__ . '/../../system/log.log';

    public static function logError(string $errorMessage): void
    {
        self::writeLog(type: "ERROR", message: $errorMessage);
    }

    public static function log(Throwable $throwable): void
    {
        $message = "Throwable:\n";
        $message .= "Message: " . $throwable->getMessage() . "\n";
        $message .= "Code: " . $throwable->getCode() . "\n";
        $message .= "File: " . $throwable->getFile() . "\n";
        $message .= "Line: " . $throwable->getLine() . "\n";
        $message .= "Trace:\n" . $throwable->getTraceAsString();

        self::writeLog(type: "Throwable", message: $message);
    }

    private static function writeLog(string $type, string $message): void
    {
        $directoryPath = dirname(self::$LOG_FILE);

        if (!is_dir($directoryPath)) {
            // CORREÇÃO: Permissões seguras (0755 em vez de 0777)
            mkdir($directoryPath, 0755, true);
        }

        $dateTime = date('Y-m-d H:i:s.v');
        $separador = str_repeat("*", 100);
        $entry = "[$dateTime] [$type] \n $message \n $separador \n";

        file_put_contents(self::$LOG_FILE, $entry, FILE_APPEND | LOCK_EX);
    }
}
