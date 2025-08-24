<?php

declare(strict_types=1);

namespace Piper\Core\Adapter;

use Piper\Contracts\AdapterInterface;

class WriteAdapter implements AdapterInterface
{
    public function __construct(
        private string $filename,
        private ?string $path = null,
        private string $format = 'json',
        private string $mode = 'w',
        private string $encoding = 'utf-8'
    ) {
        // Set default path to current directory if not provided
        if (!$this->path) {
            $this->path = getcwd();
        }
    }

    public function process(mixed $input): mixed
    {
        $fullPath = $this->path . DIRECTORY_SEPARATOR . $this->filename;

        // Ensure directory exists
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $content = $this->formatContent($input);

        $result = file_put_contents($fullPath, $content);

        if ($result === false) {
            throw new \RuntimeException("Failed to write to file: $fullPath");
        }

        // Return the input data (pass-through behavior)
        return $input;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;
        return $this;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;
        return $this;
    }

    public function setFormat(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    public function setMode(string $mode): static
    {
        $this->mode = $mode;
        return $this;
    }

    private function formatContent(mixed $input): string
    {
        return match (strtolower($this->format)) {
            'json' => json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'text', 'txt' => is_string($input) ? $input : (string) $input,
            'serialize' => serialize($input),
            'raw' => (string) $input,
            default => json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        };
    }

    public function getFullPath(): string
    {
        return $this->path . DIRECTORY_SEPARATOR . $this->filename;
    }
}
