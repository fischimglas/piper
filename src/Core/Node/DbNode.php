<?php

declare(strict_types=1);

namespace Piper\Core\Node;

class DbNode extends AbstractNode
{
    protected const ID_PREFIX = 'db_';
    protected ?string $query = null;

    public function query(string $query): static
    {
        $this->query = $query;
        return $this;
    }

    public function run(mixed $input = null): mixed
    {
        throw new \LogicException('DbNode::run() muss in einer konkreten Adapter-Node implementiert werden.');
    }
}
