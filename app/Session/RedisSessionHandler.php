<?php
declare(strict_types=1);
namespace BarCollege\Session;
use Redis;
use RuntimeException;
use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;

/** Redis stores standard PHP sessions across Vercel instances. */
final class RedisSessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    private Redis $redis;
    private int $ttl;

    public function __construct(string $url, int $ttl)
    {
        if (!class_exists(Redis::class)) {
            throw new RuntimeException('The PHP Redis extension is required.');
        }
        $parts = parse_url($url);
        $scheme = $parts['scheme'] ?? '';
        if (!is_array($parts) || !in_array($scheme, ['redis', 'rediss'], true) || empty($parts['host'])) {
            throw new RuntimeException('SESSION_REDIS_URL must be a redis:// or rediss:// URL.');
        }
        $this->ttl = max($ttl, 60);
        $this->redis = new Redis();
        $host = $scheme === 'rediss' ? 'tls://' . $parts['host'] : $parts['host'];
        $port = (int) ($parts['port'] ?? 6379);
        $context = $scheme === 'rediss'
            ? ['stream' => ['verify_peer' => true, 'verify_peer_name' => true]]
            : [];
        $this->redis->connect($host, $port, 3.0, null, 0, 3.0, $context);
        if (isset($parts['pass'])) {
            $password = rawurldecode($parts['pass']);
            $this->redis->auth(isset($parts['user']) && $parts['user'] !== ''
                ? [rawurldecode($parts['user']), $password] : $password);
        }
        $db = trim((string) ($parts['path'] ?? ''), '/');
        if ($db !== '') {
            if (!ctype_digit($db) || (int) $db > 15) {
                throw new RuntimeException('Invalid Redis database number.');
            }
            $this->redis->select((int) $db);
        }
    }

    private function key(string $id): string { return 'barcollege:sessions:' . $id; }
    public function open(string $path, string $name): bool { return true; }
    public function close(): bool { return true; }
    public function read(string $id): string|false
    {
        $value = $this->redis->get($this->key($id));
        return $value === false ? '' : $value;
    }
    public function write(string $id, string $data): bool
    {
        return $this->redis->setex($this->key($id), $this->ttl, $data);
    }
    public function destroy(string $id): bool
    {
        $this->redis->del($this->key($id));
        return true;
    }
    public function gc(int $max_lifetime): int|false { return 0; }
    public function validateId(string $id): bool
    {
        return $this->redis->exists($this->key($id)) > 0;
    }
    public function updateTimestamp(string $id, string $data): bool
    {
        return $this->redis->expire($this->key($id), $this->ttl);
    }
}
