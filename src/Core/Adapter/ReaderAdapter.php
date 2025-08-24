<?php

declare(strict_types=1);

namespace Piper\Core\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Piper\Contracts\AdapterInterface;

class ReaderAdapter implements AdapterInterface
{
    private ?Client $client = null;

    public function __construct(
        private ?string $defaultPath = null,
        private int $timeout = 10
    ) {}

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

    private function readUrl(string $url): string
    {
        try {
            $response = $this->getClient()->get($url);
            return (string) $response->getBody();
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
