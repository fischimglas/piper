<?php

namespace Piper\Contracts;

interface GraphInterface extends ExecutableInterface
{
    public function node(ExecutableInterface $nodeOrPipeOrGraph): static;
}
