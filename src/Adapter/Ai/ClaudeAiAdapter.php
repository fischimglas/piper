<?php

declare(strict_types=1);

namespace Piper\Adapter\Ai;

use Claude\Claude3Api\Client;
use Claude\Claude3Api\Config;
use Piper\Adapter\AbstractAdapter;
use Piper\Contracts\Adapter\AdapterType;
use Piper\Contracts\Adapter\AiAdapterInterface;

class ClaudeAiAdapter extends AbstractAdapter implements AiAdapterInterface
{
    private ?Client $client = null;
    private ?string $apiKey = null;
    private int $maxTokens = 1000;
    private string $model = 'claude-3-sonnet-20240229';
    private float $temperature = 0.7;
    private ?string $hostUrl = null;

    protected const ADAPTER_TYPE = AdapterType::AI;


    public function process(mixed $input): mixed
    {
        if (!$this->apiKey) {
            throw new \RuntimeException('Claude API key is required');
        }

        $response = $this->getClient()->chat((string)$input);
        $content = $response->getContent();

        return $content[0]['text'] ?? null;
    }

    // Fluent setters
    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;
        $this->client = null; // Reset client
        return $this;
    }

    public function setMaxTokens(int $maxTokens): static
    {
        $this->maxTokens = $maxTokens;
        $this->client = null; // Reset client to apply new config
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

    // Fluent getters
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function getMaxTokens(): int
    {
        return $this->maxTokens;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    private function getClient(): Client
    {
        if (!$this->client) {
            $config = new Config(
                apiKey: $this->apiKey,
                maxTokens: (string)$this->maxTokens
            );
            $this->client = new Client($config);
        }
        return $this->client;
    }

    public function setHostUrl(string $hostUrl): static
    {
        $this->hostUrl = $hostUrl;

        return $this;
    }

    public function getHostUrl(): ?string
    {
        return $this->hostUrl;
    }
}
