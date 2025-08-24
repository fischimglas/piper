<?php

namespace Piper\Contracts\Workflow;

interface StrategyInterface
{
    public function apply(mixed $input, callable $fn): mixed;
}
