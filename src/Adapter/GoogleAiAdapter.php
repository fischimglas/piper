<?php

declare(strict_types=1);

namespace Piper\Adapter;

use Exception;
use Piper\Contracts\AdapterInterface;
use Piper\Core\Cf;

class GoogleAiAdapter implements AdapterInterface
{
    private mixed $fullResponse = null;

    public function __construct(
        private ?string $apiKey = null,
        private ?string $model = null,
        private ?string $voice = null
    ) {
        Cf::autoload($this);
    }

    public static function create(
        ?string $apiKey = null,
        ?string $model = null,
        ?string $voice = null
    ): static {
        return new static(
            apiKey: $apiKey,
            model: $model,
            voice: $voice
        );
    }

    /**
     * @throws \Exception
     */
    public function process(mixed $input): mixed
    {
        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $input],
                    ],
                ],
            ],
        ];

        $options = [
            CURLOPT_URL => $this->getApiURl() . $this->apiKey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            throw new Exception('Curl error: ' . curl_error($curl));
        }

        curl_close($curl);

        $response = json_decode($response, true);
        $this->fullResponse = $response;

        // TODO
        // $response['usageMetadata']['totalTokenCount'] ?? 0;
        // $this->logTokenUsage();

        return $response['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }


    public function setVoice(): static
    {
        $this->voice = $this->voice ?? 'google-voice-3';
        return $this;
    }

    public function getVoice(): ?string
    {
        return $this->voice;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getAvailableModels(): array
    {
        // TODO
        return [];
    }

    private function getApiURl(): string
    {
        // TODO: - env variable for config? / more options (generative, chat, etc.) / set url
        return sprintf("https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=", $this->model);
    }

    // TODO
    public function getFullResponse(): mixed
    {
        return $this->fullResponse;
    }

    public function setApiKey(?string $apiKey): static
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }
}
