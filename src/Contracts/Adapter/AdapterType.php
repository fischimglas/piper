<?php

namespace Piper\Contracts\Adapter;

enum AdapterType: string
{
    case AI = 'ai';
    case IMAGE = 'image';
    case SEARCH = 'search';
    case TRANSLATE = 'translate';
    case DB = 'db';
    case TTS = 'tts';
    case GENERIC = 'generic';
    case READER = 'reader';

    case WRITER = 'writer';
}
