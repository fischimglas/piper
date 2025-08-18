<?php
/**
 * Ruft jedes einzelne elment auf und verwendet das Ergebnis für den nächsten Schritt.
 * Es wird nur das Ergebnis des letzten Schrittes zurückgegeben.
 */

namespace Piper\Strategy;

class ReduceStrategy implements StrategyInterface
{
    public function process(mixed $value, callable $processor): mixed
    {
        return array_reduce($value, fn($carry, $item) => $processor($carry, $item));
    }
}
