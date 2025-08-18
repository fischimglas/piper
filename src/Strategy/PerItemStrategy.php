<?php
declare(strict_types=1);

namespace Piper\Strategy;

use Piper\Contracts\StrategyInterface;
use Piper\Utils\CreateTrait;

class PerItemStrategy implements StrategyInterface
{

    use CreateTrait;

    public function process(mixed $value, callable $processor): mixed
    {
        // This could be run parallel, so we can use array_map to apply the processor to each item
        return array_map(fn($item) => $processor($item), (array)$value);
    }
}
