<?php

declare(strict_types=1);

namespace Piper\Contracts\Workflow;

enum Cardinality
{
    case UNIT;
    case LIST;
}
