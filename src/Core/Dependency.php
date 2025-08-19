<?php

declare(strict_types=1);

namespace Piper\Core;

use Piper\Contracts\SequenceInterface;
use Piper\Contracts\StrategyInterface;
use RuntimeException;

/**
 * Represents a dependency from one Sequence to another with an associated Strategy.
 */
class Dependency
{
    private SequenceInterface $sequence;
    private StrategyInterface $strategy;
    private ?string $alias;

    /**
     * @throws RuntimeException
     */
    public function __construct(
        SequenceInterface        $sequence,
        string|StrategyInterface $strategy,
        string                   $alias
    )
    {
        $this->setSequence($sequence)
            ->setStrategy($strategy)
            ->setAlias($alias);
    }

    public static function create(
        SequenceInterface        $sequence,
        string|StrategyInterface $strategy,
        string                   $alias
    ): self
    {
        return new self($sequence, $strategy, $alias);
    }

    /**
     * @throws RuntimeException
     */
    public function setStrategy(string|StrategyInterface $strategy): self
    {
        if (is_string($strategy)) {
            $tmp = @class_implements($strategy);
            if (!is_subclass_of($strategy, StrategyInterface::class)) {
                throw new RuntimeException(
                    sprintf('Strategy "%s" must implement %s', $strategy, StrategyInterface::class)
                );
            }
            $strategy = new $strategy();
        }

        $this->strategy = $strategy;
        return $this;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;
        return $this;
    }

    public function setSequence(SequenceInterface $sequence): self
    {
        $this->sequence = $sequence;
        return $this;
    }

    public function getSequence(): SequenceInterface
    {
        return $this->sequence;
    }

    public function getStrategy(): StrategyInterface
    {
        return $this->strategy;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * Returns the processed result of the dependency.
     * TODO: implement actual processing logic
     */
    public function getResult(): mixed
    {
        // Example: return $this->strategy->process($this->sequence->getData());
        return null;
    }
}
