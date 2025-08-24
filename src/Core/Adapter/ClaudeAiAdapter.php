<?php

declare(strict_types=1);

namespace Piper\Core\Adapter;

use Claude\Claude3Api\Client;
use Claude\Claude3Api\Config;
use Piper\Contracts\AdapterInterface;

class ClaudeAiAdapter implements AdapterInterface
{
    private ?Client $client = null;

    public function __construct(
        private ?string $apiKey = null,
        private int $maxTokens = 1000,
        private string $model = 'claude-3-sonnet-20240229',
        private float $temperature = 0.7
    ) {
        if (!$this->apiKey) {
            $this->apiKey = $_ENV['CLAUDE_API_KEY'] ?? null;
        }
    }

    public function process(mixed $input): mixed
    {
        if (!$this->apiKey) {
            throw new \RuntimeException('Claude API key is required');
        }

        $response = $this->getClient()->chat((string) $input);
        $content = $response->getContent();

        return $content[0]['text'] ?? null;
    }

    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;
        $this->client = null; // Reset client
        return $this;
    }

    public function setMaxTokens(int $maxTokens): static
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function setTemperature(float $temperature): static
    {
        $this->temperature = $temperature;
        return $this;
    }

    private function getClient(): Client
    {
        if (!$this->client) {
            $config = new Config(
                apiKey: $this->apiKey,
                maxTokens: (string) $this->maxTokens
            );
            $this->client = new Client($config);
        }
        return $this->client;
    }
}
