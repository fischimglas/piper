<?php
declare(strict_types=1);

namespace Piper\Adapter;

use ArdaGnsrn\Ollama\Ollama;
use Piper\Contracts\AdapterInterface;

class OllamaAiAdapter implements AdapterInterface
{

    private ?Ollama $client = null;

    public function __construct(
        private ?string $model = 'llama2',
        private ?string $hostUrl = null
    )
    {
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
        // TODO: Implement process() method.


    }
}
