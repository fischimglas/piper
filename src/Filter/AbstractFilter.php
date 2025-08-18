<?php
declare(strict_types=1);

namespace Piper\Filter;

class AbstractFilter
{
    public static function create(): static
    {
        return new static();
    }

    public function getName(): string
    {
        return __CLASS__;
    }

    public function format(mixed $input): mixed
    {
        return $input;
    }
}
