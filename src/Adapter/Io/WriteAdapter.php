<?php

declare(strict_types=1);

namespace Piper\Adapter\Io;

use Piper\Adapter\AbstractAdapter;
use Piper\Contracts\Adapter\AdapterType;

class WriteAdapter extends AbstractAdapter
{
    private string $filename;
    private ?string $path = null;
    private string $format = 'json';
    private string $mode = 'w';
    private string $encoding = 'utf-8';

    protected const ADAPTER_TYPE = AdapterType::WRITER;

    public function process(mixed $input): mixed
    {
        $fullPath = $this->getFullPath();

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

    // Fluent setters
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

    public function setEncoding(string $encoding): static
    {
        $this->encoding = $encoding;
        return $this;
    }

    // Fluent getters
    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function getFullPath(): string
    {
        $basePath = $this->path ?? getcwd();
        return $basePath . DIRECTORY_SEPARATOR . $this->filename;
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
}
