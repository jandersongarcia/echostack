<?php

namespace Core\Utils\Core;

use Core\Helpers\PathResolver;
use Dotenv\Dotenv;

class LanguageHelper
{
    private static bool $envLoaded = false;

    private static function ensureEnvLoaded(): void
    {
        if (!self::$envLoaded) {
            $basePath = PathResolver::basePath();
            if (file_exists($basePath . '/.env')) {
                $dotenv = Dotenv::createImmutable($basePath);
                $dotenv->load();
            }
            self::$envLoaded = true;
        }
    }

    public static function getAvailableLanguages(): array
    {
        $path = PathResolver::basePath() . '/core/lang';

        if (!is_dir($path)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($file) use ($path) {
            return is_file("$path/$file") && pathinfo($file, PATHINFO_EXTENSION) === 'php'
                ? pathinfo($file, PATHINFO_FILENAME)
                : null;
        }, scandir($path))));
    }

    public static function getDefaultLanguage(): string
    {
        self::ensureEnvLoaded();

        $default = strtolower($_ENV['APP_LANG'] ?? 'en');
        $path = PathResolver::basePath() . "/core/lang/{$default}.php";

        return is_file($path) ? $default : 'en';
    }

    public static function getDefaultLanguageFile(): string
    {
        self::ensureEnvLoaded();

        $default = strtolower($_ENV['APP_LANG'] ?? 'en');
        $path = PathResolver::basePath() . "/core/lang/{$default}.php";

        return is_file($path) ? $path : PathResolver::basePath() . "/core/lang/en.php";
    }
}
