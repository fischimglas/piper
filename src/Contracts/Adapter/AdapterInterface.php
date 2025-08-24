<?php

declare(strict_types=1);

namespace Piper\Contracts\Adapter;

interface AdapterInterface
{
    public function process(mixed $input): mixed;

    public function getAdapterType(): AdapterType;
}
