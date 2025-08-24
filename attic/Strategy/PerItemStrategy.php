<?php

declare(strict_types=1);

namespace Piper\Strategy;

use Piper\Contracts\StrategyInterface;

class PerItemStrategy implements StrategyInterface
{

    public function apply(mixed $input, callable $fn): mixed
    {
        // Wendet die Funktion auf jedes Element an (PER_ITEM)
        return array_map(fn($item) => $fn($item), (array)$input);
    }
}
