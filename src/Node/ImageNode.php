<?php

declare(strict_types=1);

namespace Piper\Node;

class ImageNode extends AbstractNode
{
    protected const ID_PREFIX = 'image_';
    protected ?string $template = null;

    public function template(string $template): static
    {
        $this->template = $template;
        return $this;
    }

    public function run(mixed $input = null): mixed
    {
        throw new \LogicException('ImageNode::run() muss in einer konkreten Adapter-Node implementiert werden.');
    }
}
