<?php

namespace Core\Utils;

use Core\Helpers\PathResolver;

class Version
{
    public static function get(): string
    {
        $composerFile = PathResolver::basePath() . DIRECTORY_SEPARATOR . 'composer.json';
        if (!file_exists($composerFile)) {
            return 'unknown';
        }

        $composerData = json_decode(file_get_contents($composerFile), true);

        return $composerData['extra']['echoapi-version'] ?? 'unknown';
    }
}
