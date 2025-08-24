<?php

declare(strict_types=1);

namespace Piper\Node;

class TextNode extends AbstractNode
{
    protected const ID_PREFIX = 'text_';
    protected ?string $template = null;


    public function template(string $template): static
    {
        $this->template = $template;
        return $this;
    }

    public function run(mixed $input = null): mixed
    {
        // Platzhalter: Implementierung durch Adapter/Kindklasse
        throw new \LogicException('TextNode::run() muss in einer konkreten Adapter-Node implementiert werden.');
    }
}
