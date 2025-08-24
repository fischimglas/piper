<?php

declare(strict_types=1);

namespace Piper\Adapter\Translate;

use DeepL\DeepLException;
use DeepL\Translator;
use Piper\Adapter\AbstractAdapter;
use Piper\Contracts\Adapter\AdapterType;

class DeeplAdapter extends AbstractAdapter
{
    private ?Translator $translator = null;
    private ?string $apiKey = null;
    private string $from = 'de';
    private string $to = 'en';
    private string $formality = 'default';
    private bool $preserveFormatting = true;
    private bool $splitSentences = true;

    protected const ADAPTER_TYPE = AdapterType::TRANSLATE;

    public function process(mixed $input): mixed
    {
        if (!$this->apiKey) {
            throw new \RuntimeException('DeepL API key is required');
        }

        $targetLang = $this->to;
        if ($targetLang === 'en') {
            $targetLang = 'en-GB';
        }

        $filtered = $input;

        // Handle arrays and filter empty content
        if (is_array($input)) {
            $filtered = array_filter($input);
            $filtered = array_filter($filtered, fn($it) => is_string($it) && trim($it));
            if (count($filtered) === 0) {
                return $input;
            }
        } elseif (is_string($input) && !strlen(trim($input))) {
            return $input;
        }

        try {
            $result = $this->getTranslator()->translateText(
                texts: $filtered,
                sourceLang: $this->from,
                targetLang: $targetLang,
                options: [
                    'preserve_formatting' => $this->preserveFormatting,
                    'split_sentences' => $this->splitSentences ? 'on' : 'off',
                    'formality' => $this->formality,
                    'tag_handling' => 'html',
                ]
            );

            if (is_array($input)) {
                $res = $input;
                $keys = array_keys($filtered);
                foreach ($result as $i => $value) {
                    $res[$keys[$i]] = $value->text;
                }
                return $res;
            } else {
                return $result->text;
            }
        } catch (DeepLException $e) {
            throw new \RuntimeException('DeepL translation failed: ' . $e->getMessage(), 0, $e);
        }
    }

    // Fluent setters
    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;
        $this->translator = null; // Reset translator
        return $this;
    }

    public function setSourceLanguage(string $from): static
    {
        $this->from = $from;
        return $this;
    }

    public function setTargetLanguage(string $to): static
    {
        $this->to = $to;
        return $this;
    }

    public function setFormality(string $formality): static
    {
        $this->formality = $formality;
        return $this;
    }

    public function setPreserveFormatting(bool $preserveFormatting): static
    {
        $this->preserveFormatting = $preserveFormatting;
        return $this;
    }

    public function setSplitSentences(bool $splitSentences): static
    {
        $this->splitSentences = $splitSentences;
        return $this;
    }

    // Fluent getters
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function getSourceLanguage(): string
    {
        return $this->from;
    }

    public function getTargetLanguage(): string
    {
        return $this->to;
    }

    public function getFormality(): string
    {
        return $this->formality;
    }

    public function getPreserveFormatting(): bool
    {
        return $this->preserveFormatting;
    }

    public function getSplitSentences(): bool
    {
        return $this->splitSentences;
    }

    private function getTranslator(): Translator
    {
        if (!$this->translator) {
            $this->translator = new Translator($this->apiKey);
        }
        return $this->translator;
    }
}
