<?php

declare(strict_types=1);

namespace Piper\Core\Adapter;

use Piper\Contracts\AdapterInterface;

class ImagineArtAiAdapter implements AdapterInterface
{
    public function __construct(
        private ?string $apiKey = null,
        private string $endpoint = 'https://api.vyro.ai/v2/image/generations',
        private string $style = 'imagine-turbo',
        private string $aspectRatio = '1:1',
        private int $seed = 5,
        private ?string $outputPath = null
    ) {
        if (!$this->apiKey) {
            $this->apiKey = $_ENV['VYRO_API_KEY'] ?? null;
        }
    }

    public function process(mixed $input): mixed
    {
        if (!$this->apiKey) {
            throw new \RuntimeException('ImagineArt API key is required');
        }

        $postFields = [
            'prompt' => (string) $input,
            'style' => $this->style,
            'aspect_ratio' => $this->aspectRatio,
            'seed' => (string) $this->seed,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postFields),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);

        if ($err = curl_error($ch)) {
            curl_close($ch);
            throw new \RuntimeException('ImagineArt API error: ' . $err);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \RuntimeException('ImagineArt API returned error code: ' . $httpCode);
        }

        $result = json_decode($response, true);

        if (!$result || !isset($result['data'][0]['url'])) {
            throw new \RuntimeException('Invalid response from ImagineArt API');
        }

        $imageUrl = $result['data'][0]['url'];

        // Download and save image if output path is specified
        if ($this->outputPath) {
            $imageData = file_get_contents($imageUrl);
            file_put_contents($this->outputPath, $imageData);
            return $this->outputPath;
        }

        return $imageUrl;
    }

    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function setStyle(string $style): static
    {
        $this->style = $style;
        return $this;
    }

    public function setAspectRatio(string $aspectRatio): static
    {
        $this->aspectRatio = $aspectRatio;
        return $this;
    }

    public function setSeed(int $seed): static
    {
        $this->seed = $seed;
        return $this;
    }

    public function setOutputPath(string $outputPath): static
    {
        $this->outputPath = $outputPath;
        return $this;
    }
}
