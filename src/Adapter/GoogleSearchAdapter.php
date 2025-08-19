<?php

declare(strict_types=1);

namespace Piper\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Piper\Contracts\AdapterInterface;
use Piper\Core\Cf;
use Piper\Utils\CreateTrait;
use RuntimeException;

class GoogleSearchAdapter implements AdapterInterface
{
    use CreateTrait;

    private Client $client;

    public function __construct(
        private array $excludedSites = [],
        private ?string $apiKey = null,
        private ?string $searchEngineId = null,
        private ?string $apiUrl = null
    ) {
        $this->client = new Client();

        Cf::autoload($this);
    }

    public static function create(
        array $excludedSites = [],
        ?string $apiKey = null,
        ?string $searchEngineId = null,
        ?string $apiUrl = null
    ): static {
        return new static(
            excludedSites: $excludedSites,
            apiKey: $apiKey,
            searchEngineId: $searchEngineId,
            apiUrl: $apiUrl
        );
    }

    public function process(mixed $input): array
    {
        if (!is_string($input) || trim($input) === '') {
            return [];
        }

        // Add -site: exclusions to query
        $excludeQuery = implode(' ', array_map(fn($site) => "-site:$site", $this->excludedSites));
        $query = trim($input . ' ' . $excludeQuery);

        try {
            $response = $this->client->get($this->apiUrl, [
                'query' => [
                    'key' => $this->apiKey,
                    'cx' => $this->searchEngineId,
                    'q' => $query,
                ],
                'timeout' => 5.0,
            ]);

            $results = json_decode($response->getBody()->getContents(), true);

            return $results['items'] ?? [];
        } catch (GuzzleException | RuntimeException $e) {
            // TODO
            return [];
        }
    }

    public function setExcludedSites(array $excludedSites): GoogleSearchAdapter
    {
        $this->excludedSites = $excludedSites;
        return $this;
    }

    public function setApiKey(?string $apiKey): GoogleSearchAdapter
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function setSearchEngineId(?string $searchEngineId): GoogleSearchAdapter
    {
        $this->searchEngineId = $searchEngineId;
        return $this;
    }

    public function setApiUrl(?string $apiUrl): GoogleSearchAdapter
    {
        $this->apiUrl = $apiUrl;
        return $this;
    }
}
