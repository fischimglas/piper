<?php

namespace Piper\Contracts;

interface StrategyInterface
{
    public function process(mixed $value, callable $processor): mixed;
}
