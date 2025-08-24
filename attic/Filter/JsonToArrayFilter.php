<?php

declare(strict_types=1);

namespace Piper\Filter;

use Piper\Contracts\FilterInterface;
use Piper\Core\AbstractFilter;
use Piper\Utils\CreateTrait;

class JsonToArrayFilter extends AbstractFilter implements FilterInterface
{
    use CreateTrait;

    public function format(mixed $input): array
    {
        $input = (string)$input;
        if (empty($input)) {
            return [];
        }
        $jsonString = $input;

        if (preg_match('/```json\s*([\[{].*?[\]}])\s*```/s', $input, $matches)) {
            $jsonString = $matches[1];
        }

        return json_decode($jsonString, true);
    }
}
