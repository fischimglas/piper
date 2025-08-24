<?php

namespace Piper\Contracts;

interface DeciderInterface extends NodeInterface
{
    public function if(callable $condition, ExecutableInterface $target): static;
    public function elseif(callable $condition, ExecutableInterface $target): static;
    public function otherwise(ExecutableInterface $target): static;
}
