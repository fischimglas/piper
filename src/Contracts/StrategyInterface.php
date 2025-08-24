<?php

namespace Piper\Contracts;

interface StrategyInterface
{
    public function apply(mixed $input, callable $fn): mixed;
}
