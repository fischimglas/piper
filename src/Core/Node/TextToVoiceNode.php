<?php

declare(strict_types=1);

namespace Piper\Core\Node;

class TextToVoiceNode extends AbstractNode
{
    protected const ID_PREFIX = 'tts_';
    protected ?string $voice = null;


    public function voice(string $voice): static
    {
        $this->voice = $voice;
        return $this;
    }

    public function run(mixed $input = null): mixed
    {
        throw new \LogicException('TextToVoiceNode::run() muss in einer konkreten Adapter-Node implementiert werden.');
    }
}
