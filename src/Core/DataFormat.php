<?php
declare(strict_types=1);

namespace Piper\Core;

enum DataFormat
{
    case ARRAY;
    case STRING;
    case ANY;
    case JSON;
}
