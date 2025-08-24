<?php

declare(strict_types=1);

namespace Piper\Strategy;

use Piper\Contracts\StrategyInterface;

class WholeResultStrategy implements StrategyInterface
{

    public function apply(mixed $input, callable $fn): mixed
    {
        // Wendet die Funktion auf das gesamte Input an (WHOLE)
        return $fn($input);
    }
}
