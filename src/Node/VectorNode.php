<?php

declare(strict_types=1);

namespace Piper\Node;

class VectorNode extends AbstractNode
{
    protected const ID_PREFIX = 'vector_';
    protected ?string $embeddingModel = null;


    public function embeddingModel(string $model): static
    {
        $this->embeddingModel = $model;
        return $this;
    }

    public function run(mixed $input = null): mixed
    {
        throw new \LogicException('VectorNode::run() muss in einer konkreten Adapter-Node implementiert werden.');
    }
}
