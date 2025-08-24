<?php

declare(strict_types=1);

namespace Piper\Filter;

use Piper\Contracts\FilterInterface;

final class ArrayMergeFilter implements FilterInterface
{
    public function apply(mixed $input): array
    {
        if (!is_array($input)) {
            return [];
        }
        return array_values(array_merge(...$input));
    }

    public static function create(): self
    {
        return new self();
    }
}
