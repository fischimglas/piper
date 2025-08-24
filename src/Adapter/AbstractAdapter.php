<?php

declare(strict_types=1);

namespace Piper\Adapter;

use Piper\Contracts\AdapterInterface;
use Piper\Factory\Cf;

abstract class AbstractAdapter implements AdapterInterface
{
    public function __construct()
    {
        // Automatically load configuration for this adapter class
        Cf::autoload($this);
    }

    /**
     * Child classes must implement the actual processing logic
     */
    abstract public function process(mixed $input): mixed;

    /**
     * Get the class name without namespace for configuration lookup
     */
    protected function getConfigKey(): string
    {
        $reflection = new \ReflectionClass($this);
        return $reflection->getShortName();
    }
}
