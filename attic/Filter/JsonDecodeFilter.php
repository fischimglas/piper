<?php

declare(strict_types=1);

namespace Piper\Filter;

use Piper\Contracts\FilterInterface;
use Piper\Core\AbstractFilter;
use Piper\Utils\CreateTrait;

class JsonDecodeFilter extends AbstractFilter implements FilterInterface
{
    use CreateTrait;

    public function __construct(private readonly ?bool $associative = true)
    {
    }

    public static function create(?bool $associative = true): static
    {
        return new static(associative: $associative);
    }

    public function format(mixed $input): mixed
    {
        return json_decode(trim($input . ''));
    }
}
