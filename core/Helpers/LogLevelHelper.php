<?php

namespace Core\Helpers;

use Monolog\Level;

class LogLevelHelper
{
    public static function getTelegramLogLevel(): Level
    {
        $envCategories = $_ENV['ERROR_NOTIFY_CATEGORIES'] ?? 'critical';
        $levels = array_map('strtolower', array_map('trim', explode(',', $envCategories)));

        $levelMap = [
            'debug' => Level::Debug,
            'info' => Level::Info,
            'notice' => Level::Notice,
            'warning' => Level::Warning,
            'error' => Level::Error,
            'critical' => Level::Critical,
            'alert' => Level::Alert,
            'emergency' => Level::Emergency
        ];

        $minLevel = Level::Emergency;

        foreach ($levels as $levelName) {
            if (isset($levelMap[$levelName])) {
                $candidateLevel = $levelMap[$levelName];
                if ($candidateLevel->value < $minLevel->value) {
                    $minLevel = $candidateLevel;
                }
            }
        }

        return $minLevel;
    }
}
