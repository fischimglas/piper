<?php
declare(strict_types=1);

namespace Piper\Adapter;

use Piper\Contracts\AdapterInterface;
use Piper\Core\Cf;

class MistralAiAdapter implements AdapterInterface
{

    public function __construct(
        private ?string $apiKey = null,
        private ?string $model = 'mistral-medium-2508',
    )
    {
        Cf::autoload($this);
    }

    public static function create(?string $apiKey = null, ?string $model = 'mistral-medium-2508'): static
    {
        return new static(apiKey: $apiKey, model: $model);
    }

    public function process(mixed $input): mixed
    {
        $endpoint = 'https://api.mistral.ai/v1/chat/completions';
        $model = 'mistral-large-latest'; // Specify the model you wish to use

        // Prepare the request payload
        $data = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $input]
            ]
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        $result = null;
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            // Decode and display the response
            $responseData = json_decode($response, true);
            $result = $responseData['choices'][0]['message']['content'] ?? null;
        }

        curl_close($ch);

        return $result;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): MistralAiAdapter
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): MistralAiAdapter
    {
        $this->model = $model;
        return $this;
    }
}
