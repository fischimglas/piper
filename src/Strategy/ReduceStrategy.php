<?php
declare(strict_types=1);

namespace Piper\Strategy;

use Piper\Contracts\StrategyInterface;
use Piper\Utils\CreateTrait;

class ReduceStrategy implements StrategyInterface
{
    use CreateTrait;

    public function process(mixed $value, callable $processor): mixed
    {
        return array_reduce($value, fn($carry, $item) => $processor($carry, $item));
    }
}
