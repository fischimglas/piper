<?php
declare(strict_types=1);

namespace Piper\Adapter;

use Piper\Contracts\AdapterInterface;
use Piper\Core\Cf;
use RuntimeException;

class ImagineArtAiAdapter implements AdapterInterface
{

    public function __construct(
        private ?string $apiKey = null,
        private ?string $endpoint = 'https://api.vyro.ai/v2/image/generations',
        private ?string $style = 'realistic',
        private ?string $aspect_ratio = '1:1',
        private ?int    $seed = 5

    )
    {
        Cf::autoload($this);
    }

    public function getStyle(): ?string
    {
        return $this->style;
    }

    public function setStyle(?string $style): ImagineArtAiAdapter
    {
        $this->style = $style;
        return $this;
    }

    public function getAspectRatio(): ?string
    {
        return $this->aspect_ratio;
    }

    public function setAspectRatio(?string $aspect_ratio): ImagineArtAiAdapter
    {
        $this->aspect_ratio = $aspect_ratio;
        return $this;
    }

    public function getSeed(): ?int
    {
        return $this->seed;
    }

    public function setSeed(?int $seed): ImagineArtAiAdapter
    {
        $this->seed = $seed;
        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): ImagineArtAiAdapter
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function setEndpoint(?string $endpoint): ImagineArtAiAdapter
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function process(mixed $input): mixed
    {
        $postFields = [
            'prompt' => $input,
        ];
        $postFields['style'] = $this->style;
        $postFields['aspect_ratio'] = $this->aspect_ratio;
        $postFields['seed'] = (string)$this->seed; // API expects seed as string
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS => $postFields,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new RuntimeException('Curl error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new RuntimeException("API request failed with status $httpCode: $response");
        }

        file_put_contents('test.jpg', $response); // Save the image to a file for debugging

        // API returns raw image data (binary) or JSON with url — depends on provider
        return $response;


    }
}
