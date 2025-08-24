<?php

declare(strict_types=1);

namespace Piper\Adapter;

use Piper\Contracts\Adapter\AdapterInterface;
use Piper\Contracts\Adapter\AdapterType;
use Piper\Factory\Cf;

abstract class AbstractAdapter implements AdapterInterface
{
    protected const ADAPTER_TYPE = AdapterType::GENERIC;

    public function __construct()
    {
        // Automatically load configuration for this adapter class
        Cf::autoload($this);
    }

    /**
     * Child classes must implement the actual processing logic
     */
    abstract public function process(mixed $input): mixed;

    public function getAdapterType(): AdapterType
    {
        return self::ADAPTER_TYPE;
    }

    /**
     * Get the class name without namespace for configuration lookup
     */
    protected function getConfigKey(): string
    {
        $reflection = new \ReflectionClass($this);
        return $reflection->getShortName();
    }
}
