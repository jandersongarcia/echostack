<?php

namespace Core\Utils\System;

class Diagnostics
{
    private static float $startTime;
    private static array $startUsage;

    public static function start(): void
    {
        self::$startTime = microtime(true);
        self::$startUsage = getrusage();
    }

    public static function end(): array
    {
        $endTime = microtime(true);
        $endUsage = getrusage();

        $executionTimeMs = round(($endTime - self::$startTime) * 1000, 2);

        $cpuStart = self::$startUsage;
        $cpuEnd = $endUsage;

        $cpuTime = (
            ($cpuEnd["ru_utime.tv_sec"] - $cpuStart["ru_utime.tv_sec"]) +
            ($cpuEnd["ru_stime.tv_sec"] - $cpuStart["ru_stime.tv_sec"]) +
            ($cpuEnd["ru_utime.tv_usec"] - $cpuStart["ru_utime.tv_usec"]) / 1e6 +
            ($cpuEnd["ru_stime.tv_usec"] - $cpuStart["ru_stime.tv_usec"]) / 1e6
        );

        $memoryUsageMB = round(memory_get_peak_usage(true) / 1048576, 2);

        return [
            'execution_time_ms' => $executionTimeMs,
            'cpu_time_sec' => round($cpuTime, 4),
            'memory_peak_mb' => $memoryUsageMB,
        ];
    }
}
