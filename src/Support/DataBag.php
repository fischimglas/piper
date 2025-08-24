<?php
declare(strict_types=1);

namespace Piper\Support;

use Piper\Contracts\DataBagInterface;

final class DataBag implements DataBagInterface
{
    private array $data = [];

    public function set(string $key, mixed $value): static
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function all(): array
    {
        return $this->data;
    }

    public function remove(string $key): static
    {
        unset($this->data[$key]);
        return $this;
    }

    public function clear(): static
    {
        $this->data = [];
        return $this;
    }

    public function copy(): static
    {
        $clone = new self();
        $clone->data = $this->data;
        return $clone;
    }
}
