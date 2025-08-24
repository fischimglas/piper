<?php

namespace Piper\Contracts;

enum CacheStrategy
{
    case DISABLED;
    case PER_RUN;
    case PER_INPUT;
    case GLOBAL;
}
