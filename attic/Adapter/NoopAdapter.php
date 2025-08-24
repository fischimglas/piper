<?php

declare(strict_types=1);

namespace Piper\Adapter;

use Piper\Contracts\AdapterInterface;

class NoopAdapter implements AdapterInterface
{

    public static function create(): static
    {
        return new static();
    }

    public function process(mixed $input): mixed
    {
        return $input;
    }

}
