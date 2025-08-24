<?php

declare(strict_types=1);

namespace Piper\Filter;

use Piper\Contracts\FilterInterface;
use Piper\Core\AbstractFilter;
use Piper\Utils\CreateTrait;

class TrimFilter extends AbstractFilter implements FilterInterface
{
    use CreateTrait;

    public function format(mixed $input): string
    {
        return trim($input);
    }
}
