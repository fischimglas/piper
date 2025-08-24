<?php

declare(strict_types=1);

namespace Piper\Adapter\Io;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Piper\Adapter\AbstractAdapter;
use Piper\Contracts\Adapter\AdapterType;

class ReaderAdapter extends AbstractAdapter
{
    private ?Client $client = null;
    private ?string $defaultPath = null;
    private int $timeout = 10;
    protected const ADAPTER_TYPE = AdapterType::READER;

    public function process(mixed $input): mixed
    {
        $source = $input ?? $this->defaultPath;

        if (!$source) {
            throw new \RuntimeException('No source provided for reading');
        }

        // Handle URL input
        if (filter_var($source, FILTER_VALIDATE_URL)) {
            return $this->readUrl($source);
        }

        // Handle file path input
        if (is_string($source) && is_file($source) && is_readable($source)) {
            return file_get_contents($source);
        }

        throw new \RuntimeException("Cannot read source: $source");
    }

    // Fluent setters
    public function setDefaultPath(string $path): static
    {
        $this->defaultPath = $path;
        return $this;
    }

    public function setTimeout(int $timeout): static
    {
        $this->timeout = $timeout;
        $this->client = null; // Reset client
        return $this;
    }

    // Fluent getters
    public function getDefaultPath(): ?string
    {
        return $this->defaultPath;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    private function readUrl(string $url): string
    {
        try {
            $response = $this->getClient()->get($url);
            return (string)$response->getBody();
        } catch (GuzzleException $e) {
            throw new \RuntimeException("Failed to fetch URL: $url. " . $e->getMessage(), 0, $e);
        }
    }

    private function getClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client([
                'timeout' => $this->timeout,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (compatible; PiperReader/1.0)',
                ],
            ]);
        }
        return $this->client;
    }
}
