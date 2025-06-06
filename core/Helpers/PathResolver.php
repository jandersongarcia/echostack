<?php

namespace Core\Helpers;

class PathResolver
{
    public static function basePath(): string
    {
        return dirname(__DIR__, 2);
    }

    public static function configPath(string $path = ''): string
    {
        return self::basePath() . DIRECTORY_SEPARATOR . 'config' . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    public static function logsPath(string $path = ''): string
    {
        return self::basePath() . DIRECTORY_SEPARATOR . 'logs' . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    public static function envPath(): string
    {
        return self::basePath() . DIRECTORY_SEPARATOR . '.env';
    }

    public static function srcPath(string $path = ''): string
    {
        return self::basePath() . DIRECTORY_SEPARATOR . 'src' . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    public static function routesPath(string $path = ''): string
    {
        return self::basePath() . DIRECTORY_SEPARATOR . 'routes' . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    public static function scriptsPath(string $path = ''): string
    {
        return self::basePath() . DIRECTORY_SEPARATOR . 'scripts' . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    public static function composerPath(): string
    {
        return self::basePath() . DIRECTORY_SEPARATOR . 'composer.json';
    }
}
