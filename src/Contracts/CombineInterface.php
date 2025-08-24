<?php

namespace Piper\Contracts;

interface CombineInterface extends ExecutableInterface
{
    public function add(ExecutableInterface $executable): static;

    public function combineWith(callable $combineFn): static;

    public function merge(): static;

    public function zip(): static;

    public function collect(): static;
}
