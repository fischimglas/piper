<?php

namespace Piper\Contracts\Node;

use Piper\Contracts\Workflow\ExecutableInterface;
use Piper\Contracts\Workflow\StrategyInterface;

interface AiNodeInterface extends NodeInterface
{
    public function dependsOn(ExecutableInterface $nodeOrPipeOrGraph, StrategyInterface $strategy): static;

    public function setTemplate(?string $template): static;
}
