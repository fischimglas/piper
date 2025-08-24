<?php

declare(strict_types=1);

namespace Piper\Adapter;

use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;
use Piper\Contracts\AdapterInterface;
use Piper\Core\Cf;

class GoogleAiAdapter implements AdapterInterface
{
    private mixed $fullResponse = null;
    private ?Client $client = null;
    private ?string $systemInstruction = null;

    public function __construct(
        private ?string $apiKey = null,
        private ?string $model = ModelName::GEMINI_1_5_FLASH,
        private ?string $voice = null
    )
    {
        Cf::autoload($this);
    }

    public static function create(
        ?string $apiKey = null,
        ?string $model = ModelName::GEMINI_1_5_FLASH,
        ?string $voice = null
    ): static
    {
        return new static(
            apiKey: $apiKey,
            model: $model,
            voice: $voice
        );
    }

    public function client()
    {
        if (!$this->client) {
            $this->client = new Client($this->apiKey);
        }
        return $this->client;
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function process(mixed $input): mixed
    {
        $client = $this->client();
        $response = $client->withV1BetaVersion()
            ->generativeModel($this->model)
            // ->withSystemInstruction('You are a cat. Your name is Neko.')
            ->generateContent(
                new TextPart($input),
            );

        return $response->text();
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
        return (array)$this->client()->listModels();
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

    public function setSystemInstruction(?string $systemInstruction): GoogleAiAdapter
    {
        $this->systemInstruction = $systemInstruction;
        return $this;
    }

    public function getSystemInstruction(): ?string
    {
        return $this->systemInstruction;
    }
}
