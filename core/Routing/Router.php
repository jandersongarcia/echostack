<?php

namespace Core\Routing;

use AltoRouter;

class Router
{
    private static array $middlewareGroupStack = [];
    private static array $prefixStack = [];

    /**
     * Agrupa rotas com prefixo (string) ou middlewares (array)
     * Suporta chamada encadeada de prefixo + middlewares
     */
    public static function group($config, callable $routes): void
    {
        global $router;

        if (!$router instanceof AltoRouter) {
            throw new \RuntimeException("Global \$router is not an instance of AltoRouter.");
        }

        if (is_string($config)) {
            // Empilha prefixo
            $prefix = preg_replace_callback(
                '#^/v(\d+)$#i',
                fn($m) => '/V' . $m[1],
                rtrim($config, '/')
            );
            self::$prefixStack[] = $prefix;

            $routes();

            array_pop(self::$prefixStack);
        } elseif (is_array($config)) {
            // Empilha middlewares
            $parent = self::getCurrentGroupMiddlewares();
            self::$middlewareGroupStack[] = array_merge($parent, $config);

            $routes();

            array_pop(self::$middlewareGroupStack);
        } else {
            throw new \InvalidArgumentException("Invalid parameter passed to Router::group()");
        }
    }

    /**
     * Grupo exclusivo de middlewares, respeitando o prefixo atual
     */
    public static function groupMiddlewares(array $middlewares, callable $routes): void
    {
        $parent = self::getCurrentGroupMiddlewares();
        self::$middlewareGroupStack[] = array_merge($parent, $middlewares);

        $routes();

        array_pop(self::$middlewareGroupStack);
    }

    /**
     * Agrupa rotas com prefixo e middlewares ao mesmo tempo
     */
    public static function groupWithPrefixAndMiddlewares(string $prefix, array $middlewares, callable $routes): void
    {
        self::group($prefix, function () use ($middlewares, $routes) {
            self::group($middlewares, $routes);
        });
    }

    /**
     * Retorna o prefixo atual empilhado (ou vazio)
     */
    public static function getCurrentPrefix(): string
    {
        return self::$prefixStack ? implode('', self::$prefixStack) : '';
    }

    /**
     * Retorna a pilha atual de middlewares aplicados
     */
    public static function getCurrentGroupMiddlewares(): array
    {
        return self::$middlewareGroupStack ? end(self::$middlewareGroupStack) : [];
    }

    /**
     * Registra rota com prefixo + middlewares (se existirem)
     */
    public static function map(string $method, string $route, $target): void
    {
        global $router;

        $fullRoute = self::getCurrentPrefix() . $route;
        $middlewares = self::getCurrentGroupMiddlewares();

        if (!empty($middlewares)) {
            $target = array_merge($middlewares, [$target]);
        }

        $router->map($method, $fullRoute, $target);
    }

    /**
     * Retorna instância ativa do AltoRouter (preenchido via global)
     */
    public static function getRouter(): \AltoRouter
    {
        global $router;
        return $router;
    }
}
