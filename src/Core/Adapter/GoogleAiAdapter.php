<?php

declare(strict_types=1);

namespace Piper\Core\Adapter;

use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;
use Piper\Contracts\AdapterInterface;

class GoogleAiAdapter implements AdapterInterface
{
    private ?Client $client = null;

    public function __construct(
        private ?string $apiKey = null,
        private string $model = ModelName::GEMINI_1_5_FLASH,
        private ?string $systemInstruction = null,
        private float $temperature = 0.7,
        private int $maxTokens = 1000
    ) {
        if (!$this->apiKey) {
            $this->apiKey = $_ENV['GOOGLE_API_KEY'] ?? null;
        }
    }

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

    private function getClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client($this->apiKey);
        }
        return $this->client;
    }
}
