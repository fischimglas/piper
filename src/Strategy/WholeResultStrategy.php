<?php

namespace Piper\Strategy;

class WholeResultStrategy implements StrategyInterface
{

    public static function create(): static
    {
        return new static();
    }

    public function process(mixed $value, callable $processor): mixed
    {
        return $processor($value);
    }
}
