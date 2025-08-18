<?php
declare(strict_types=1);

namespace Piper\Adapter;

abstract class AbstractAdapter implements AdapterInterface
{

    public static function create(): static
    {
        return new static();
    }

    abstract public function process(mixed $input): mixed;

}
