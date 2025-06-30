<?php

namespace Core\Helpers;

class MiddlewareHelper
{
    /**
     * Sanitiza a chave de cache removendo caracteres reservados
     *
     * @param string $prefix
     * @param string $ip
     * @return string
     */
    public static function sanitizeCacheKey(string $prefix, string $ip): string
    {
        // Substitui caracteres reservados por _
        $safeIp = str_replace(
            [':', '{', '}', '(', ')', '/', '\\', '@'],
            '_',
            $ip
        );

        return $prefix . '.' . $safeIp;
    }

    public static function sanitizeCacheKeyRaw(string $key): string
    {
        return str_replace(
            [':', '{', '}', '(', ')', '/', '\\', '@'],
            '_',
            $key
        );
    }
}
