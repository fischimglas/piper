<?php

declare(strict_types=1);

namespace Piper\Core\Adapter;

use Piper\Contracts\AdapterInterface;

class ElevenLabsAiAdapter implements AdapterInterface
{
    public function __construct(
        private ?string $apiKey = null,
        private string $model = 'eleven_multilingual_v2',
        private string $voiceId = 'MFZUKuGQUsGJPQjTS4wC',
        private float $stability = 0.75,
        private float $similarityBoost = 0.75,
        private ?string $outputPath = null
    ) {
        if (!$this->apiKey) {
            $this->apiKey = $_ENV['ELEVENLABS_API_KEY'] ?? null;
        }
    }

    public function process(mixed $input): mixed
    {
        if (!$this->apiKey) {
            throw new \RuntimeException('ElevenLabs API key is required');
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.elevenlabs.io/v1/text-to-speech/" . $this->voiceId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                "model_id" => $this->model,
                "text" => (string) $input,
                "voice_settings" => [
                    "stability" => $this->stability,
                    "similarity_boost" => $this->similarityBoost
                ]
            ]),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "xi-api-key: " . $this->apiKey,
            ],
        ]);

        $response = curl_exec($curl);

        if ($err = curl_error($curl)) {
            curl_close($curl);
            throw new \RuntimeException('ElevenLabs API error: ' . $err);
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 200) {
            throw new \RuntimeException('ElevenLabs API returned error code: ' . $httpCode);
        }

        // Save to file if output path is specified
        if ($this->outputPath) {
            file_put_contents($this->outputPath, $response);
            return $this->outputPath;
        }

        // Return binary audio data
        return $response;
    }

    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function setVoiceId(string $voiceId): static
    {
        $this->voiceId = $voiceId;
        return $this;
    }

    public function setStability(float $stability): static
    {
        $this->stability = $stability;
        return $this;
    }

    public function setSimilarityBoost(float $similarityBoost): static
    {
        $this->similarityBoost = $similarityBoost;
        return $this;
    }

    public function setOutputPath(string $outputPath): static
    {
        $this->outputPath = $outputPath;
        return $this;
    }
}
