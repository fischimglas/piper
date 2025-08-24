<?php

declare(strict_types=1);

namespace Piper\Core\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Piper\Contracts\AdapterInterface;

class GoogleSearchAdapter implements AdapterInterface
{
    private ?Client $client = null;

    public function __construct(
        private ?string $apiKey = null,
        private ?string $searchEngineId = null,
        private string $apiUrl = 'https://www.googleapis.com/customsearch/v1',
        private array $excludedSites = [],
        private int $maxResults = 10
    ) {
        if (!$this->apiKey) {
            $this->apiKey = $_ENV['GOOGLE_SEARCH_API_KEY'] ?? null;
        }
        if (!$this->searchEngineId) {
            $this->searchEngineId = $_ENV['GOOGLE_SEARCH_ENGINE_ID'] ?? null;
        }
    }

    public function process(mixed $input): mixed
    {
        if (!$this->apiKey || !$this->searchEngineId) {
            throw new \RuntimeException('Google Search API key and Search Engine ID are required');
        }

        if (!is_string($input) || trim($input) === '') {
            return [];
        }

        // Add -site: exclusions to query
        $excludeQuery = implode(' ', array_map(fn($site) => "-site:$site", $this->excludedSites));
        $query = trim($input . ' ' . $excludeQuery);

        try {
            $response = $this->getClient()->get($this->apiUrl, [
                'query' => [
                    'key' => $this->apiKey,
                    'cx' => $this->searchEngineId,
                    'q' => $query,
                    'num' => $this->maxResults,
                ],
                'timeout' => 10.0,
            ]);

            $results = json_decode($response->getBody()->getContents(), true);

            return $results['items'] ?? [];
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Google Search API error: ' . $e->getMessage(), 0, $e);
        }
    }

    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function setSearchEngineId(string $searchEngineId): static
    {
        $this->searchEngineId = $searchEngineId;
        return $this;
    }

    public function setExcludedSites(array $excludedSites): static
    {
        $this->excludedSites = $excludedSites;
        return $this;
    }

    public function setMaxResults(int $maxResults): static
    {
        $this->maxResults = $maxResults;
        return $this;
    }

    private function getClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client();
        }
        return $this->client;
    }
}
