<?php

declare(strict_types=1);

namespace Piper\Node;

use LogicException;

class WriterNode extends AbstractNode
{
    protected const ID_PREFIX = 'writer_';
    protected ?string $filePath = null;

    public function filePath(string $path): static
    {
        $this->filePath = $path;
        return $this;
    }

    public function run(mixed $input = null): mixed
    {
        throw new LogicException('WriterNode::run() muss in einer konkreten Adapter-Node implementiert werden.');
    }
}
