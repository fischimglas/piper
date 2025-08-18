<?php

namespace Piper\Strategy;

interface StrategyInterface
{
    public function process(mixed $value, callable $processor): mixed;
}
