<?php

declare(strict_types=1);

namespace Piper\Contracts;

interface AdapterInterface
{
    public function process(mixed $input): mixed;
}
