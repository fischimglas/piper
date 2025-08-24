<?php
declare(strict_types=1);

namespace Piper\Adapter;

use Claude\Claude3Api\Client;
use Claude\Claude3Api\Config;
use Piper\Contracts\AdapterInterface;
use Piper\Core\Cf;

class ClaudeAiAdapter implements AdapterInterface
{

    private ?Client $client = null;

    private ?int $maxTokens = 1000;

    public function __construct(private ?string $apiKey = null)
    {
        Cf::autoload($this);
    }

    public static function create(?string $apiKey = null): static
    {
        return new static(apiKey: $apiKey);
    }

    public function client(): Client
    {
        if ($this->client) {
            return $this->client;
        }
        $config = new Config(apiKey: $this->apiKey, maxTokens: (string)$this->maxTokens);
        $this->client = new Client($config);

        return $this->client;
    }


    public function process(mixed $input): mixed
    {
        $response = $this->client()->chat($input);
        print_r($response->getContent());
        return $response->getContent()[0]['text'] ?? null;
    }

    public function setApiKey(?string $apiKey): ClaudeAiAdapter
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setMaxTokens(?int $maxTokens): ClaudeAiAdapter
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }

    public function getMaxTokens(): ?int
    {
        return $this->maxTokens;
    }
}
