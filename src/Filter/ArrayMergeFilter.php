<?php
declare(strict_types=1);

namespace Piper\Filter;

class ArrayMergeFilter extends AbstractFilter implements FilterInterface
{

    public function format(mixed $input): array
    {
        return array_values(array_merge(...$input));
    }
}
