<?php
/**
 * TODO: Implement Google Text-to-Speech adapter.
 * @see https://cloud.google.com/text-to-speech/docs/reference/rest/v1/text/synthesize
 */
declare(strict_types=1);

namespace Piper\Adapter;

use Piper\Contracts\AdapterInterface;
use Piper\Core\Cf;

class GoogleTextToSpeech implements AdapterInterface
{
    protected ?string $credentialsFile = null;

    public function __construct(private ?string $apiKey = null)
    {
        Cf::autoload($this);
    }

    public static function create(?string $apiKey = null): static
    {
        return new static(apiKey: $apiKey);
    }

    public function process(mixed $input): mixed
    {
        $url = "https://texttospeech.googleapis.com/v1/text:synthesize?key={$this->apiKey}";

        $postData = [
            "input" => ["text" => $input],
            "voice" => [
                "languageCode" => "en-US",
                "name" => "en-US-Standard-C",
                "ssmlGender" => "FEMALE"
            ],
            "audioConfig" => ["audioEncoding" => "MP3"]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['audioContent'])) {
            file_put_contents("output.mp3", base64_decode($data['audioContent']));
            echo "✅ Audio generated: output.mp3\n";
        } else {
            echo "❌ Error: " . $response . "\n";
        }

        return '';
    }

    public function setApiKey(?string $apiKey): GoogleTextToSpeech
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }
}


// This requires an identity file and the Google Cloud PHP library. Nice but later.
//        // Initialize the Text-to-Speech client
//        $client = new TextToSpeechClient([
//            'credentials' => $this->credentialsFile
//        ]);
//
//        // Define text to synthesize
//        $inputX = new SynthesisInput();
//        $inputX->setText($input);
//
//        // Choose voice and language
//        $voice = new VoiceSelectionParams();
//        $voice->setLanguageCode('en-US');
//        // Optionally: $voice->setSsmlGender(/* VOICE GENDER */);
//        // Optionally: $voice->setName('en-US-Wavenet-C');
//
//        // Configure output audio format
//        $audioConfig = new AudioConfig();
//        $audioConfig->setAudioEncoding(AudioEncoding::MP3);
//
//        // Build request
//        $ssr = (new SynthesizeSpeechRequest())
//            ->setInput($inputX)
//            ->setVoice($voice)
//            ->setAudioConfig($audioConfig);
//
//        // Perform synthesis
//        $response = $client->synthesizeSpeech($ssr);
//
//        // Save audio to file
//        file_put_contents('output.mp3', $response->getAudioContent());
//
//        echo "Audio content written to output.mp3\n";
//        $client->close();
//
//        return '';
