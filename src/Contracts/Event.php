<?php

declare(strict_types=1);

namespace Piper\Contracts;

enum Event
{
    case BEFORE_RUN;
    case AFTER_RUN;
    case ON_ERROR;
}
