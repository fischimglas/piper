<?php

namespace Piper\Strategy;

class PerItemStrategy implements StrategyInterface
{

    public static function create(): static
    {
        return new self();
    }

    public function process(mixed $value, callable $processor): mixed
    {
        // This can be run parallel, so we can use array_map to apply the processor to each item
        return array_map(fn($item) => $processor($item), (array)$value);
    }
}
