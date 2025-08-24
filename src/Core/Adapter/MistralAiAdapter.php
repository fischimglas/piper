<?php

declare(strict_types=1);

namespace Piper\Core\Adapter;

use Piper\Contracts\AdapterInterface;

class MistralAiAdapter implements AdapterInterface
{
    public function __construct(
        private ?string $apiKey = null,
        private string $model = 'mistral-large-latest',
        private float $temperature = 0.7,
        private int $maxTokens = 1000
    ) {
        if (!$this->apiKey) {
            $this->apiKey = $_ENV['MISTRAL_API_KEY'] ?? null;
        }
    }

    public function process(mixed $input): mixed
    {
        if (!$this->apiKey) {
            throw new \RuntimeException('Mistral API key is required');
        }

        $endpoint = 'https://api.mistral.ai/v1/chat/completions';

        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => (string) $input]
            ],
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('Mistral API error: ' . $error);
        }

        curl_close($ch);

        $responseData = json_decode($response, true);

        if (!$responseData || !isset($responseData['choices'][0]['message']['content'])) {
            throw new \RuntimeException('Invalid response from Mistral API');
        }

        return $responseData['choices'][0]['message']['content'];
    }

    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;
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

    public function setMaxTokens(int $maxTokens): static
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }
}
