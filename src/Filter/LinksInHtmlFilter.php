<?php
declare(strict_types=1);

namespace Piper\Filter;

use Piper\Model\DataFormat;

class LinksInHtmlFilter implements FilterInterface
{

    public function __construct()
    {
    }

    public static function create(): static
    {
        return new static();
    }

    public function format(mixed $input): mixed
    {
        preg_match_all('/<a\s+href=["\']([^"\']+)["\']/', $input, $matches);

        return array_unique($matches[1]);
    }

    public function getInputFormat(): DataFormat
    {
        return DataFormat::STRING;
    }

    public function getOutputFormat(): DataFormat
    {
        return DataFormat::ARRAY;
    }

    public function getName(): string
    {
        return __CLASS__;
    }
}
