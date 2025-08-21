<?php
declare(strict_types=1);

namespace Piper\Adapter;

use Piper\Contracts\AdapterInterface;
use Piper\Core\Cf;

class ElevenLabsAiAdapter implements AdapterInterface
{

    public function __construct(
        private ?string $apiKey = null,
        private ?string $model = 'eleven_multilingual_v2',
        private ?string $voiceId = 'MFZUKuGQUsGJPQjTS4wC'
    )
    {
        Cf::autoload($this);
    }

    public static function create(
        ?string $apiKey = null,
        ?string $model = 'eleven_multilingual_v2',
        ?string $voiceId = 'MFZUKuGQUsGJPQjTS4wC'
    ): static
    {
        return new static(apiKey: $apiKey, model: $model, voiceId: $voiceId);
    }


    public function process(mixed $input): mixed
    {
        $curl = curl_init();

        // Set cURL options
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
                "text" => $input,
                "voice_settings" => [
                    "stability" => 0.75,
                    "similarity_boost" => 0.75
                ]
            ]),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "xi-api-key: " . $this->apiKey,
            ],
        ]);
        $response = curl_exec($curl);

        // Check for cURL errors
        if ($err = curl_error($curl)) {
            echo "cURL Error #: " . $err;
        } else {
            file_put_contents("output.mp3", $response);
        }

        curl_close($curl);

        return 'Audio file generated' ?? null;
    }

    public function setVoiceId(?string $voiceId): ElevenLabsAiAdapter
    {
        $this->voiceId = $voiceId;
        return $this;
    }

    public function setModel(?string $model): ElevenLabsAiAdapter
    {
        $this->model = $model;
        return $this;
    }

    public function getVoiceId(): ?string
    {
        return $this->voiceId;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setApiKey(?string $apiKey): ElevenLabsAiAdapter
    {
        $this->apiKey = $apiKey;
        return $this;
    }
}
