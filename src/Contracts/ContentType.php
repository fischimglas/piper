<?php

namespace Piper\Contracts;

enum ContentType
{
    case TEXT;
    case IMAGE;
    case AUDIO;
    case OBJECT;
    case FILE;
    case VECTOR;
}
