<?php

namespace App\Controllers;

use Medoo\Medoo;
use Symfony\Component\HttpFoundation\JsonResponse;
use Monolog\Logger;

/**
 * @OA\PathItem(path="/v1/health")
 * @OA\Tag(name="System", description="System endpoints")
 */
class HealthController
{
    private Medoo $db;
    private Logger $logger;

    public function __construct(Medoo $db, Logger $logger)
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
     *                 "logs": "ok",
     *                 "telegram": "configured"
     *             }
     *         )
     *     )
     * )
     */
    public function check(): JsonResponse
    {
        return new JsonResponse([
            'version' => $this->getVersion(),
            'database' => $this->checkDatabase(),
            'logs' => $this->checkLogsDirectory(),
            'telegram' => $this->checkTelegram(),
        ]);
    }

    private function checkDatabase(): string
    {
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
        $logDir = realpath(__DIR__ . '/../../storage/logs');

        if (!$logDir || !is_writable($logDir)) {
            return 'fail';
        }

        $files = glob($logDir . '/*.log');

        return (!empty($files)) ? 'ok' : 'warn_empty';
    }

    private function checkTelegram(): string
    {
        $token = $_ENV['TELEGRAM_BOT_TOKEN'] ?? null;
        $chatId = $_ENV['TELEGRAM_CHAT_ID'] ?? null;

        return ($token && $chatId) ? 'configured' : 'not_configured';
    }

    private function getVersion(): string
    {
        $composer = json_decode(file_get_contents(__DIR__ . '/../../composer.json'), true);
        return $composer['extra']['echoapi-version'] ?? 'unknown';
    }
}
