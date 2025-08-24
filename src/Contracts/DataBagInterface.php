<?php

namespace Piper\Contracts;

interface DataBagInterface
{
    public function set(string $key, mixed $value): static;
    public function get(string $key, mixed $default = null): mixed;
    public function has(string $key): bool;
    public function all(): array;
    public function remove(string $key): static;
    public function clear(): static;
    public function copy(): static;
}
