<?php

namespace Piper\Contracts\Workflow;

interface GraphInterface extends ExecutableInterface
{
    public function node(ExecutableInterface $nodeOrPipeOrGraph): static;
}
