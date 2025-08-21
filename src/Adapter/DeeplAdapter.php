<?php

declare(strict_types=1);

namespace Piper\Adapter;

use DeepL\DeepLException;
use DeepL\Translator;
use Piper\Contracts\AdapterInterface;
use Piper\Core\Cf;

class DeeplAdapter implements AdapterInterface
{
    private Translator $translator;

    private string $from = 'de';

    private string $to = 'en';

    public static function create(string $from, string $to): static
    {
        $el = new static();
        $el->setFrom($from);
        $el->setTo($to);
        return $el;
    }

    /**
     * @throws DeepLException
     */
    public function __construct(
        private ?string $apiKey = null,
        private ?string $formality = 'default',
        private bool    $preserveFormatting = true,
        private bool    $splitSentences = true,
    )
    {
        Cf::autoload($this);

        if ($this->apiKey) {
            $this->translator = new Translator($this->apiKey);
        }
    }

    /**
     * @throws \DeepL\DeepLException
     */
    public function process(mixed $input): mixed
    {
        $to = $this->getTo();
        if ($to === 'en') {
            $this->setTo('en-GB');
        }


        // TODO this code is just a temporary copy.
        $filtered = $input;
        // Make sure we only translate elements with actual content
        if (is_array($input)) {
            $filtered = array_filter($input);
            $filtered = array_filter($filtered, fn($it) => is_string($it));
            if (count($filtered) === 0) {
                return $input;
            }
        } elseif (is_string($input) && !strlen(trim($input))) {
            return $input;
        }

        $result = $this->translator->translateText(
            texts: $filtered,
            sourceLang: $this->from,
            targetLang: $this->to,
            options: [
                'preserve_formatting' => $this->isPreserveFormatting(),
                'split_sentences' => $this->isSplitSentences() === true ? 'on' : 'off',
                'formality' => $this->getFormality(),
                'tag_handling' => 'html',
            ]
        );

        if (is_array($input)) {
            $res = $input;
            $keys = array_keys($filtered);
            foreach ($result as $i => $value) {
                $res[$keys[$i]] = $value->text;
            }
        } else {
            $res = $result->text;
        }

        return $res;
    }

    public function getFormality(): ?string
    {
        return $this->formality;
    }

    public function setFormality(?string $formality): DeeplAdapter
    {
        $this->formality = $formality;
        return $this;
    }

    public function isPreserveFormatting(): bool
    {
        return $this->preserveFormatting;
    }

    public function setPreserveFormatting(bool $preserveFormatting): DeeplAdapter
    {
        $this->preserveFormatting = $preserveFormatting;
        return $this;
    }

    public function isSplitSentences(): bool
    {
        return $this->splitSentences;
    }

    public function setSplitSentences(bool $splitSentences): DeeplAdapter
    {
        $this->splitSentences = $splitSentences;
        return $this;
    }

    /**
     * @throws \DeepL\DeepLException
     */
    public function setApiKey(?string $apiKey): static
    {
        $this->apiKey = $apiKey;

        $this->translator = new Translator($this->apiKey);

        return $this;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): DeeplAdapter
    {
        $this->from = $from;
        return $this;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): DeeplAdapter
    {
        $this->to = $to;
        return $this;
    }
}
