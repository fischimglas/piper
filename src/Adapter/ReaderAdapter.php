<?php
declare(strict_types=1);

namespace Piper\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Piper\Core\Cf;
use RuntimeException;

class ReaderAdapter implements AdapterInterface
{
    private Client $client;

    public function __construct(
        private ?string $filePath = null
    )
    {
        $this->client = new Client([
            'timeout' => 5.0,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; PiperReader/1.0)',
            ],
        ]);

        Cf::autoload($this);
    }

    public function process(mixed $input): string
    {
        if (is_null($input) && $this->getFilePath()) {
            $input = $this->getFilePath();
        }

        echo "ReaderAdapter: Processing input...:" . $input . "\n";
        if (filter_var($input, FILTER_VALIDATE_URL)) {
            return $this->readUrl($input);
        }

        if (is_file($input) && is_readable($input)) {
            return file_get_contents($input);
        }

        throw new RuntimeException("ReaderAdapter: Cannot read input: $input");
    }

    private function readUrl(string $url): string
    {
        try {
            $response = $this->client->get($url);
            return (string)$response->getBody();
        } catch (GuzzleException $e) {
            throw new RuntimeException("Failed to fetch URL: $url. " . $e->getMessage());
        }
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): void
    {
        $this->filePath = $filePath;
    }
}
