<?php

namespace Piper\Core;

use Piper\Contracts\StrategyInterface;

enum Strategy implements StrategyInterface
{
    case WHOLE;
    case PER_ITEM;

    public function apply(mixed $input, callable $fn): mixed
    {
        return match ($this) {
            self::WHOLE => $fn($input),
            self::PER_ITEM => is_iterable($input)
                ? array_map(fn($item) => $fn($item), is_array($input) ? $input : iterator_to_array($input))
                : $fn($input),
        };
    }
}
