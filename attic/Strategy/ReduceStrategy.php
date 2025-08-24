<?php

declare(strict_types=1);

namespace Piper\Strategy;

use Piper\Contracts\StrategyInterface;

class ReduceStrategy implements StrategyInterface
{

    public function apply(mixed $input, callable $fn, mixed $initial = null): mixed
    {
        // Reduziert das Input mit der Funktion und Initialwert
        return array_reduce($input, $fn, $initial);
    }
}
