<?php

namespace Piper\Contracts;

interface FilterInterface
{
    public function apply(mixed $input): mixed;
}
