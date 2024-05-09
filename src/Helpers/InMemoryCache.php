<?php

namespace Brightfish\MediaAnalyzer\Helpers;

use DateInterval;
use DateTime;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class InMemoryCache implements CacheInterface
{
    protected array $cache = [];

    /** @inheritDoc */
    public function get(string $key, mixed $default = null): mixed
    {
        if (! isset($this->cache[$key]) || $this->isExpired($key)) {
            return $default;
        }

        return $this->cache[$key][1];
    }

    /**
     * @throws InvalidArgumentException
     */
    private function isExpired(string $key): bool
    {
        $expire = $this->cache[$key][0];

        if (null !== $expire && $expire < time()) {
            // If a ttl was set, and it expired in the past, invalidate the cache.
            $this->delete($key);

            return true;
        }

        return false;
    }

    /** @inheritDoc */
    public function set(string $key, mixed $value, DateInterval|int $ttl = null): bool
    {
        $expire = null;

        if (isset($ttl)) {
            if ($ttl instanceof DateInterval) {
                $expire = (new DateTime('now'))->add($ttl)->getTimeStamp();
            } else {
                $expire = time() + $ttl;
            }
        }

        $this->cache[$key] = [$expire, $value];

        return true;
    }

    /** @inheritDoc */
    public function delete(string $key): bool
    {
        unset($this->cache[$key]);

        return true;
    }

    /** @inheritDoc */
    public function clear(): bool
    {
        $this->cache = [];

        return true;
    }

    /** @inheritDoc */
    public function has(string $key): bool
    {
        return isset($this->cache[$key]) && ! $this->isExpired($key);
    }

    /** @inheritDoc */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        foreach ($keys as $key) {
            yield $key => $this->get($key, $default);
        }
    }

    /** @inheritDoc */
    public function setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool
    {
        $result = true;

        foreach ($values as $key => $value) {
            if (! $this->set($key, $value, $ttl)) {
                $result = false;
            }
        }

        return $result;
    }

    /** @inheritDoc */
    public function deleteMultiple(iterable $keys): bool
    {
        $result = true;

        foreach ($keys as $key) {
            if (! $this->delete($key)) {
                $result = false;
            }
        }

        return $result;
    }
}
