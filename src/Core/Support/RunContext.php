<?php

namespace Piper\Core\Support;

final class RunContext
{
    private static ?string $current = null;

    public static function start(): string
    {
        return self::$current = bin2hex(random_bytes(8));
    }

    public static function current(): ?string
    {
        return self::$current;
    }
}
