<?php

namespace Piper\Contracts;

interface PipeInterface extends ExecutableInterface
{
    public function input(array $data): static;

    public function pipe(ExecutableInterface $nodeOrPipe): static;
}
