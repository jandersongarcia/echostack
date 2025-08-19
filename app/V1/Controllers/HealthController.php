<?php

namespace V1\Controllers;

use Medoo\Medoo;
use Symfony\Component\HttpFoundation\JsonResponse;
use Monolog\Logger;
use Core\Helpers\PathResolver;

/**
 * @OA\PathItem(path="/v1/health")
 * @OA\Tag(name="System", description="System endpoints")
 */
class HealthController
{
    private ?Medoo $db;
    private Logger $logger;

    public function __construct(?Medoo $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * @OA\Get(
     *     path="/v1/health",
     *     summary="System health check",
     *     tags={"System"},
     *     @OA\Response(
     *         response=200,
     *         description="Returns the current status of the API subsystems",
     *         @OA\JsonContent(
     *             example={
     *                 "database": "connected",
     *                 "logs": "activated",
     *                 "telegram": "configured"
     *             }
     *         )
     *     )
     * )
     */
    public function check(): JsonResponse
    {
        return new JsonResponse([
            'database' => $this->checkDatabase(),
            'logs' => $this->checkLogsDirectory(),
            'telegram' => $this->checkTelegram(),
        ]);
    }

    private function checkDatabase(): string
    {
        if (!$this->db) {
            return 'no_database';
        }

        try {
            $this->db->query('SELECT 1');
            return 'connected';
        } catch (\Throwable $e) {
            $this->logger->error('Database check failed', ['message' => $e->getMessage()]);
            return 'fail';
        }
    }

    private function checkLogsDirectory(): string
    {
        $logDir = realpath(PathResolver::basePath() . '/storage/logs');
        $hasHandler = false;

        foreach ($this->logger->getHandlers() as $handler) {
            if ($handler instanceof \Monolog\Handler\StreamHandler) {
                $hasHandler = true;

                try {
                    $reflection = new \ReflectionClass($handler);
                    $streamProp = $reflection->getProperty('url');
                    $streamProp->setAccessible(true);
                    $logFile = $streamProp->getValue($handler);

                    if (!is_writable(dirname($logFile))) {
                        return 'unwritable';
                    }
                } catch (\Throwable $e) {
                    return 'fail';
                }
            }
        }

        if (!$hasHandler) {
            return 'no_handler';
        }

        $files = $logDir ? glob($logDir . '/*.log') : [];

        return (!empty($files)) ? 'ok' : 'warn_empty';
    }

    private function checkTelegram(): string
    {
        $token = $_ENV['TELEGRAM_BOT_TOKEN'] ?? null;
        $chatId = $_ENV['TELEGRAM_CHAT_ID'] ?? null;

        return ($token && $chatId) ? 'configured' : 'not_configured';
    }

}
