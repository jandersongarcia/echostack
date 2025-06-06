<?php
namespace App\Utils;

use Monolog\Logger;

class ErrorHandler
{
    public static function register(Logger $logger)
    {
        set_exception_handler(function ($e) use ($logger) {
            $logger->error('Uncaught Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            self::renderJsonError('Internal Server Error', 500);
        });

        set_error_handler(function ($severity, $message, $file, $line) use ($logger) {
            $logger->error('PHP Error', [
                'severity' => $severity,
                'message' => $message,
                'file' => $file,
                'line' => $line
            ]);

            self::renderJsonError('Internal Server Error', 500);
        });

        register_shutdown_function(function () use ($logger) {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                $logger->critical('Fatal Error', $error);
                self::renderJsonError('Fatal Internal Server Error', 500);
            }
        });
    }

    private static function renderJsonError($message, $code = 500)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ]);
    }
}
