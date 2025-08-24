<?php

namespace Piper\Core\Cache;

use Piper\Contracts\CacheInterface;

final class InMemoryCache implements CacheInterface
{
    private array $store = [];
    private array $expiries = [];

    public function get(string $key): mixed
    {
        if (!$this->has($key)) {
            return null;
        }
        return $this->store[$key];
    }

    public function set(string $key, mixed $value, int $ttlSeconds = 0): static
    {
        $this->store[$key] = $value;
        $this->expiries[$key] = $ttlSeconds > 0 ? time() + $ttlSeconds : 0;
        return $this;
    }

    public function has(string $key): bool
    {
        if (!array_key_exists($key, $this->store)) {
            return false;
        }
        $exp = $this->expiries[$key] ?? 0;
        if ($exp > 0 && $exp < time()) {
            unset($this->store[$key], $this->expiries[$key]);
            return false;
        }
        return true;
    }

    public function delete(string $key): static
    {
        unset($this->store[$key], $this->expiries[$key]);
        return $this;
    }

    public function clear(): static
    {
        $this->store = [];
        $this->expiries = [];
        return $this;
    }
}
