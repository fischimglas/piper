<?php
/**
 * TODO:
 * - The receipt should be available in the sequence. but it shhould also be filled with some (in/out?) data
 * while piping.
 */
declare(strict_types=1);

namespace Piper\Core;

class Receipt
{
    private array $data = [];
    private array $logs = [];

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function log(string $sequenceName, mixed $message): void
    {
        $this->logs[] = [
            'time' => microtime(true),
            'sequence' => $sequenceName,
            'message' => $message,
        ];
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function all(): array
    {
        return $this->data;
    }
}
