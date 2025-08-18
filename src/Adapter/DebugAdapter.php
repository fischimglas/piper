<?php

namespace Piper\Adapter;

class DebugAdapter implements AdapterInterface
{
    public static function create(): static
    {
        return new static();
    }

    public function process(mixed $input): mixed
    {
        echo '- DEBUG adapter: ' . $input . PHP_EOL;

        return 'X' . $input . 'X';

    }
}
