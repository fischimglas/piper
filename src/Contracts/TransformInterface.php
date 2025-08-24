<?php

namespace Piper\Contracts;

interface TransformInterface extends ExecutableInterface
{
    public function map(callable $fn): static;

    public function filter(callable $fn): static;

    public function reduce(callable $fn, mixed $initial): static;
}
