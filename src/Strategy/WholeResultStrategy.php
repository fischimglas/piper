<?php

declare(strict_types=1);

namespace Piper\Strategy;

use Piper\Contracts\StrategyInterface;
use Piper\Utils\CreateTrait;

class WholeResultStrategy implements StrategyInterface
{
    use CreateTrait;

    public function process(mixed $value, callable $processor): mixed
    {
        return $processor($value);
    }
}
