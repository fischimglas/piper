<?php
/**
 * This is part of the development and should be removed later on.
 */

namespace Piper\Utils;

trait CreateTrait
{
    public static function create(): static
    {
        return new self();
    }
}
