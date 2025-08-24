<?php
declare(strict_types=1);

namespace Piper\Adapter;

use ArdaGnsrn\Ollama\Ollama;
use Piper\Contracts\AdapterInterface;
use Piper\Core\Cf;

class OllamaAiAdapter implements AdapterInterface
{

    private ?Ollama $client = null;

    public function __construct(
        private ?string $model = 'smollm:latest',
        private ?string $hostUrl = 'http://piper-ollama-1:11434'
    )
    {
        Cf::autoload($this);
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): OllamaAiAdapter
    {
        $this->model = $model;
        return $this;
    }

    public function getHostUrl(): ?string
    {
        return $this->hostUrl;
    }

    public function setHostUrl(?string $hostUrl): OllamaAiAdapter
    {
        $this->hostUrl = $hostUrl;
        return $this;
    }


    public function client()
    {
        if ($this->client) {
            return $this->client;
        }
        $this->client = Ollama::client($this->hostUrl);
        return $this->client;
    }

    public function process(mixed $input): mixed
    {
        $completions = $this->client()->completions()->create([
            'model' => $this->model,
            'prompt' => $input,
        ]);

        return $completions->response;
    }
}
