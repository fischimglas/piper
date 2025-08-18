<?php

namespace Piper\Filter;

class JsonDecodeFilter extends AbstractFilter implements FilterInterface
{
    public function __construct(?bool $associative = true)
    {
    }

    public static function create(?bool $associative = true): static
    {
        return new static($associative);
    }

    public function format(mixed $input): mixed
    {
        return json_decode(trim($input));
    }
}
