<?php

namespace Piper\Contracts;

interface CacheInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttlSeconds = 0): static;
    public function has(string $key): bool;
    public function delete(string $key): static;
    public function clear(): static;
}
