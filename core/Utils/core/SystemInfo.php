<?php

namespace Core\Utils\Core;

use Core\Helpers\PathResolver;

class SystemInfo
{
    public static function name(): string
    {
        return 'EchoStack';
    }

    public static function version(): string
    {
        $composerFile = PathResolver::composerPath();
        if (!file_exists($composerFile)) {
            return 'unknown';
        }

        $composerData = json_decode(file_get_contents($composerFile), true);

        return $composerData['extra']['echoapi-version'] ?? 'unknown';
    }

    public static function motto(): string
    {
        return 'Live long and prosper 🖖';
    }

    public static function fullSignature(): string
    {
        return sprintf(
            "%s - v: %s | %s",
            self::name(),
            self::version(),
            self::motto()
        );
    }
}
