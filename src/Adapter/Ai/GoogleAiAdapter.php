<?php

declare(strict_types=1);

namespace Piper\Adapter\Ai;

use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;
use Piper\Adapter\AbstractAdapter;

class GoogleAiAdapter extends AbstractAdapter
{
    private ?Client $client = null;
    private ?string $apiKey = null;
    private string $model = ModelName::GEMINI_1_5_FLASH;
    private ?string $systemInstruction = null;
    private float $temperature = 0.7;
    private int $maxTokens = 1000;

    public function process(mixed $input): mixed
    {
        if (!$this->apiKey) {
            throw new \RuntimeException('Google AI API key is required');
        }

        $client = $this->getClient();

        $model = $client->withV1BetaVersion()->generativeModel($this->model);

        if ($this->systemInstruction) {
            $model = $model->withSystemInstruction($this->systemInstruction);
        }

        $response = $model->generateContent(new TextPart((string) $input));

        return $response->text();
    }

    // Fluent setters
    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;
        $this->client = null; // Reset client
        return $this;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function setSystemInstruction(string $instruction): static
    {
        $this->systemInstruction = $instruction;
        return $this;
    }

    public function setTemperature(float $temperature): static
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function setMaxTokens(int $maxTokens): static
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }

    // Fluent getters
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getSystemInstruction(): ?string
    {
        return $this->systemInstruction;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function getMaxTokens(): int
    {
        return $this->maxTokens;
    }

    private function getClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client($this->apiKey);
        }
        return $this->client;
    }
}
