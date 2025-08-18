<?php
declare(strict_types=1);

namespace Piper\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GoogleSearchAdapter extends AbstractAdapter implements AdapterInterface
{

    private Client $client;
    private string $apiKey = '';
    private string $searchEngineId = '';

    /** @var string[] */
    private array $excludedSites = [
        'facebook.com',
        'twitter.com',
        'instagram.com',
        'paropakaram.com',
        'youtube.com',
        'pinterest.com',
        'linkedin.com',
        'reddit.com',
        'quora.com',
        'tiktok.com',
        'amazon.com',
        'ebay.com',
        'alibaba.com',
        'aliexpress.com',
        'etsy.com',
        'walmart.com',
        'bestbuy.com',
        'target.com',
        'flipkart.com',
        'snapdeal.com',
        'olx.in',
        'quikr.com',
    ];

    public function __construct()
    {
        // $this->apiKey = $apiKey;
        // $this->searchEngineId = $searchEngineId;
        $this->client = new Client();
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
            $response = $this->client->get('https://www.googleapis.com/customsearch/v1', [
                'query' => [
                    'key' => $this->apiKey,
                    'cx' => $this->searchEngineId,
                    'q' => $query,
                ],
                'timeout' => 5.0,
            ]);

            $results = json_decode($response->getBody()->getContents(), true);

            return $results['items'] ?? [];
        } catch (GuzzleException|\RuntimeException $e) {
            // Optional: log error here
            return [];
        }
    }
}
