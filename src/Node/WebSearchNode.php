<?php

declare(strict_types=1);

namespace Piper\Node;

class WebSearchNode extends AbstractNode
{
    protected const ID_PREFIX = 'websearch_';
    protected ?string $query = null;


    public function query(string $query): static
    {
        $this->query = $query;
        return $this;
    }

    public function run(mixed $input = null): mixed
    {
        throw new \LogicException('WebSearchNode::run() muss in einer konkreten Adapter-Node implementiert werden.');
    }
}
