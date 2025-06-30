<?php
namespace Core\Services;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\ApcuAdapter;

class CacheService
{
    private $adapter;
    private ?object $redisNative = null;
    private bool $supportsAtomicIncrement = false;

    public function __construct()
    {
        // Filesystem como fallback garantido
        $filesystemAdapter = new FilesystemAdapter(
            namespace: '',
            defaultLifetime: 0,
            directory: __DIR__ . '/../../storage/cache'
        );

        // 1 Tenta Redis se a extensão existir
        if (class_exists('\Redis')) {
            try {
                $redisHost = getenv('REDIS_HOST') ?: '127.0.0.1';
                $redisPort = getenv('REDIS_PORT') ?: '6379';
                $redisPassword = getenv('REDIS_PASSWORD') !== 'null' ? getenv('REDIS_PASSWORD') : null;
                $redisDb = getenv('REDIS_DB') ?: 0;

                $redisDsn = sprintf(
                    'redis://%s%s:%s/%s',
                    $redisPassword ? ':' . $redisPassword . '@' : '',
                    $redisHost,
                    $redisPort,
                    $redisDb
                );

                $client = RedisAdapter::createConnection($redisDsn);
                $this->adapter = new RedisAdapter($client);

                // Redis nativo para increment()
                $this->redisNative = new \Redis();
                $this->redisNative->connect($redisHost, $redisPort);
                if ($redisPassword) {
                    $this->redisNative->auth($redisPassword);
                }
                $this->redisNative->select($redisDb);

                $this->supportsAtomicIncrement = true;
                return;
            } catch (\Throwable $e) {
                // Falhou, segue tentando
            }
        }

        // 2️ Tenta APCu se a extensão existir
        if (function_exists('apcu_enabled')) {
            $this->adapter = new ApcuAdapter();
            $this->supportsAtomicIncrement = false;
            return;
        }

        // 3️ Default absoluto: Filesystem
        $this->adapter = $filesystemAdapter;
        $this->supportsAtomicIncrement = false;
    }

    public function set(string $key, mixed $value, int $ttl = 300): bool
    {
        $item = $this->adapter->getItem($key);
        $item->set($value);
        $item->expiresAfter($ttl);
        return $this->adapter->save($item);
    }

    public function get(string $key): mixed
    {
        $item = $this->adapter->getItem($key);
        return $item->isHit() ? $item->get() : null;
    }

    public function delete(string $key): bool
    {
        return $this->adapter->deleteItem($key);
    }

    public function clear(): bool
    {
        return $this->adapter->clear();
    }

    /**
     * Incrementa contador (atômico no Redis, simulado caso contrário).
     */
    public function increment(string $key, int $ttl): int
    {
        if ($this->supportsAtomicIncrement && $this->redisNative) {
            if (!$this->redisNative->exists($key)) {
                $this->redisNative->setex($key, $ttl, 1);
                return 1;
            }
            return (int)$this->redisNative->incr($key);
        }

        // Fallback não atômico
        $current = $this->get($key) ?? 0;
        $current++;
        $this->set($key, $current, $ttl);

        error_log("[CacheService] Aviso: usando incremento não-atômico. Para melhor performance, instale Redis.");
        return $current;
    }
}
