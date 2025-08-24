<?php

declare(strict_types=1);

namespace Piper\Adapter\Ai;

use ArdaGnsrn\Ollama\Ollama;
use Piper\Adapter\AbstractAdapter;

class OllamaAiAdapter extends AbstractAdapter
{
    private ?Ollama $client = null;
    private string $model = 'smollm:latest';
    private string $hostUrl = 'http://localhost:11434';

    public function process(mixed $input): mixed
    {
        $completions = $this->getClient()->completions()->create([
            'model' => $this->model,
            'prompt' => (string) $input,
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

    public function getHostUrl(): string
    {
        return $this->hostUrl;
    }

    private function getClient(): Ollama
    {
        if (!$this->client) {
            $this->client = Ollama::client($this->hostUrl);
        }
        return $this->client;
    }
}
