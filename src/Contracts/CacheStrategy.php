<?php

declare(strict_types=1);

namespace Piper\Contracts;

enum CacheStrategy
{
    case DISABLED;
    case PER_RUN;
    case PER_INPUT;
    case GLOBAL;
}
