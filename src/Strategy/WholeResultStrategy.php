<?php

namespace Piper\Strategy;

use Piper\Utils\CreateTrait;

class WholeResultStrategy implements StrategyInterface
{

    use CreateTrait;

    public function process(mixed $value, callable $processor): mixed
    {
        return $processor($value);
    }
}
