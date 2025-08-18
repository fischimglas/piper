<?php
declare(strict_types=1);

namespace Piper\Filter;

class AbstractFilter
{

    public function getName(): string
    {
        return __CLASS__;
    }
}
