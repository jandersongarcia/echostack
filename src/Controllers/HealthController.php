<?php

namespace App\Controllers;

use Medoo\Medoo;
use Symfony\Component\HttpFoundation\JsonResponse;
use Monolog\Logger;

/**
 * @OA\PathItem(path="/")
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
     *     path="/",
     *     summary="Health check",
     *     tags={"System"},
     *     @OA\Response(
     *         response=200,
     *         description="API status check",
     *         @OA\JsonContent(
     *             example={
     *                 "pong": true,
     *                 "database": "ok",
     *                 "filesystem": "ok",
     *                 "telegram": "configured",
     *                 "version": "2.0.0"
     *             }
     *         )
     *     )
     * )
     */
    public function check(): JsonResponse
    {
        $health = [
            'pong' => true,
            'database' => $this->checkDatabase(),
            'filesystem' => $this->checkFilesystem(),
            'telegram' => $this->checkTelegram(),
            'version' => $this->getVersion()
        ];

        return new JsonResponse($health);
    }

    private function checkDatabase(): string
    {
        try {
            $this->db->query('SELECT 1');
            return 'ok';
        } catch (\Throwable $e) {
            $this->logger->error('Database check failed', ['message' => $e->getMessage()]);
            return 'fail';
        }
    }

    private function checkFilesystem(): string
    {
        return is_writable(__DIR__ . '/../../storage/logs') ? 'ok' : 'fail';
    }

    private function checkTelegram(): string
    {
        $token = $_ENV['TELEGRAM_BOT_TOKEN'] ?? null;
        $chatId = $_ENV['TELEGRAM_CHAT_ID'] ?? null;

        return ($token && $chatId) ? 'configured' : 'not_configured';
    }

    private function getVersion(): string
    {
        $versionConfig = require __DIR__ . '/../../config/version.php';
        return $versionConfig['version'] ?? 'unknown';
    }
}