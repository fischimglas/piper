<?php

declare(strict_types=1);

namespace Piper\Adapter\Ai;

use ArdaGnsrn\Ollama\Ollama;
use Piper\Adapter\AbstractAdapter;
use Piper\Contracts\Adapter\AdapterType;
use Piper\Contracts\Adapter\AiAdapterInterface;

class OllamaAiAdapter extends AbstractAdapter implements AiAdapterInterface
{
    private ?Ollama $client = null;
    private string $model = 'smollm:latest';
    private string $hostUrl = 'http://localhost:11434';
    private ?string $apiKey = null;

    protected const ADAPTER_TYPE = AdapterType::AI;

    public function process(mixed $input): mixed
    {
        $completions = $this->getClient()->completions()->create([
            'model' => $this->model,
            'prompt' => (string)$input,
        ]);

        return $completions->response;
    }

    // Fluent setters
    public function setModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function setHostUrl(string $hostUrl): static
    {
        $this->hostUrl = $hostUrl;
        $this->client = null; // Reset client
        return $this;
    }

    // Fluent getters
    public function getModel(): string
    {
        return $this->model;
    }

    private function getClient(): Ollama
    {
        if (!$this->client) {
            $this->client = Ollama::client($this->hostUrl);
        }
        return $this->client;
    }

    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getHostUrl(): ?string
    {
        return $this->hostUrl;
    }
}
