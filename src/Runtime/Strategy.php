<?php
declare(strict_types=1);

namespace Piper\Runtime;

use Piper\Contracts\StrategyInterface;

enum Strategy implements StrategyInterface
{
    case WHOLE;
    case PER_ITEM;
    case REDUCE;

    public function apply(mixed $input, callable $fn): mixed
    {
        return match ($this) {
            self::WHOLE => $fn($input),

            self::PER_ITEM => is_iterable($input)
                ? array_map(fn($item) => $fn($item), is_array($input) ? $input : iterator_to_array($input))
                : $fn($input),

            self::REDUCE => is_iterable($input)
                ? array_reduce(
                    is_array($input) ? $input : iterator_to_array($input),
                    fn($carry, $item) => $fn($item), // Fixed: proper reduce callback signature
                    null // Initial value
                )
                : $fn($input),
        };
    }
}
