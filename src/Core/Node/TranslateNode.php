<?php

declare(strict_types=1);

namespace Piper\Core\Node;

class TranslateNode extends AbstractNode
{
    protected const ID_PREFIX = 'translate_';
    protected ?string $sourceLang = null;
    protected ?string $targetLang = null;

    public function sourceLang(string $lang): static
    {
        $this->sourceLang = $lang;
        return $this;
    }

    public function targetLang(string $lang): static
    {
        $this->targetLang = $lang;
        return $this;
    }

    public function run(mixed $input = null): mixed
    {
        throw new \LogicException('TranslateNode::run() muss in einer konkreten Adapter-Node implementiert werden.');
    }
}
