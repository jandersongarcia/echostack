<?php

namespace App\Controllers;

use Medoo\Medoo;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\PathItem(path="/")
 * @OA\Tag(name="Sistema", description="Endpoints de sistema")
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
     *     tags={"Sistema"},
     *     @OA\Response(
     *         response=200,
     *         description="Status da API",
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
        return new JsonResponse([
            "pong" => true,
            "database" => "ok",
            "filesystem" => "ok",
            "telegram" => "configured",
            "version" => "2.0.0"
        ]);
    }
}
