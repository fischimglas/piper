<?php

namespace Piper\Contracts\Node;

use Piper\Contracts\Workflow\ExecutableInterface;
use Piper\Contracts\Workflow\StrategyInterface;

interface NodeInterface extends ExecutableInterface
{
    public function dependsOn(ExecutableInterface $nodeOrPipeOrGraph, StrategyInterface $strategy): static;
}
