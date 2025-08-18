<?php
declare(strict_types=1);

namespace Piper\Adapter;

interface AdapterInterface
{

    public function process(mixed $input): mixed;

}
