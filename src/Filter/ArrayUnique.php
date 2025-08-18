<?php
declare(strict_types=1);

namespace Piper\Filter;

use Piper\Utils\CreateTrait;

class ArrayUnique extends AbstractFilter implements FilterInterface
{
    use CreateTrait;

    public function format(mixed $input): array
    {
        return array_values(array_unique($input));
    }
}
