<?php

declare(strict_types=1);

namespace Piper\Adapter\Search;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Piper\Adapter\AbstractAdapter;
use Piper\Contracts\Adapter\AdapterType;

class GoogleSearchAdapter extends AbstractAdapter
{
    private ?Client $client = null;
    private ?string $apiKey = null;
    private ?string $searchEngineId = null;
    private string $apiUrl = 'https://www.googleapis.com/customsearch/v1';
    private array $excludedSites = [];
    private int $maxResults = 10;

    protected const ADAPTER_TYPE = AdapterType::SEARCH;

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

    // Fluent setters
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

    public function setApiUrl(string $apiUrl): static
    {
        $this->apiUrl = $apiUrl;
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

    // Fluent getters
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function getSearchEngineId(): ?string
    {
        return $this->searchEngineId;
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    public function getExcludedSites(): array
    {
        return $this->excludedSites;
    }

    public function getMaxResults(): int
    {
        return $this->maxResults;
    }

    private function getClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client();
        }
        return $this->client;
    }
}
