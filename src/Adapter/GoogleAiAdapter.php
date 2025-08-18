<?php
declare(strict_types=1);

namespace Piper\Adapter;

use Exception;

class GoogleAiAdapter extends AbstractAdapter implements AdapterInterface
{
    private const string API_KEY = 'AIzaSyBFO2-GstfLOf8fgRlGgukUpkqVEKyXmeM';

    public function __construct(
        protected ?string $model = 'gemini-2.0-flash',
        protected ?string $voice = 'google-voice-3'
    )
    {

    }


    /**
     * @throws \Exception
     */
    public function process(mixed $input): mixed
    {

        echo '- prompt: ' . $input . PHP_EOL;
        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $input],
                    ],
                ],
            ],
        ];


        $options = [
            CURLOPT_URL => $this->getApiURl() . self::API_KEY,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            throw new Exception('Curl error: ' . curl_error($curl));
        }

        curl_close($curl);

        $response = json_decode($response, true);

        // $this->tokensUsed = $response['usageMetadata']['totalTokenCount'] ?? 0;
        // $this->logTokenUsage();

        return $response['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }


    public function setVoice(): static
    {
        $this->voice = $this->voice ?? 'google-voice-3';
        return $this;
    }

    public function getVoice(): ?string
    {
        return $this->voice;
    }


    public function setModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getAvailableModels(): array
    {
        return [];
    }

    private function getApiURl(): string
    {
        return sprintf("https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=", $this->model);
    }
}
