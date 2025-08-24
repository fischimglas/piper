<?php

namespace Piper\Core;

enum Event
{
    case BEFORE_RUN;
    case AFTER_RUN;
    case ON_ERROR;
}
