<?php

declare(strict_types=1);

namespace Piper\Core\Adapter;

use ArdaGnsrn\Ollama\Ollama;
use Piper\Contracts\AdapterInterface;

class OllamaAiAdapter implements AdapterInterface
{
    private ?Ollama $client = null;

    public function __construct(
        private string $model = 'smollm:latest',
        private string $hostUrl = 'http://localhost:11434'
    ) {}

    public function process(mixed $input): mixed
    {
        $completions = $this->getClient()->completions()->create([
            'model' => $this->model,
            'prompt' => (string) $input,
        ]);

        return $completions->response;
    }

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

    private function getClient(): Ollama
    {
        if (!$this->client) {
            $this->client = Ollama::client($this->hostUrl);
        }
        return $this->client;
    }
}
