<?php
declare(strict_types=1);

namespace Piper\Filter;

use Piper\Utils\CreateTrait;

class JsonDecodeFilter extends AbstractFilter implements FilterInterface
{
    use CreateTrait;

    public static function create(?bool $associative = true): static
    {
        return new static($associative);
    }

    public function format(mixed $input): mixed
    {
        return json_decode(trim($input));
    }
}
