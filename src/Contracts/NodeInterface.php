<?php

namespace Piper\Contracts;

interface NodeInterface extends ExecutableInterface
{
    public function dependsOn(ExecutableInterface $nodeOrPipeOrGraph, StrategyInterface $strategy): static;
}
