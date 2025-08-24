<?php

declare(strict_types=1);

namespace Piper\Node;

use Piper\Contracts\Adapter\AdapterInterface;
use Piper\Contracts\Adapter\AiAdapterInterface;
use Piper\Contracts\Node\AiNodeInterface;

class AiNode extends AbstractNode implements AiNodeInterface
{
    protected const ID_PREFIX = 'ai_';

    // protected ?string $template = null;
    private ?string $template = null;

    public function run(mixed $input = null): mixed
    {
        // Platzhalter: Implementierung durch Adapter/Kindklasse
        throw new \LogicException('TextNode::run() muss in einer konkreten Adapter-Node implementiert werden.');
    }

    public function withAdapter(AdapterInterface $adapter): static
    {
        if (!$adapter instanceof AiAdapterInterface) {
            throw new \InvalidArgumentException('Der Adapter muss eine Instanz von AiAdapterInterface sein.');
        }
        $this->adapter = $adapter;
        return $this;
    }

    public function setTemplate(?string $template): static
    {
        $this->template = $template;
        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }
}
