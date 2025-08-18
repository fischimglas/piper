<?php
declare(strict_types=1);

namespace Piper\Model;

use Piper\Sequence\SequenceInterface;
use Piper\Strategy\StrategyInterface;
use RuntimeException;

class Dependency
{
    private SequenceInterface $sequence;
    private StrategyInterface $strategy;
    private ?string $alias;

    public function __construct(SequenceInterface $sequence, string|StrategyInterface $strategy, string $alias)
    {
        $this->setStrategy($strategy);
        $this->setSequence($sequence);
        $this->setAlias($alias);
    }

    public static function create(
        SequenceInterface        $sequence,
        string|StrategyInterface $strategy,
        string                   $alias
    ): self
    {
        return new self($sequence, $strategy, $alias);
    }

    public function getSequence(): SequenceInterface
    {
        return $this->sequence;
    }

    public function getStrategy(): StrategyInterface
    {
        return $this->strategy;
    }

    public function getResult(): mixed
    {
        return $this->strategy->process($this->sequence->getData());
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setSequence(SequenceInterface $sequence): self
    {
        $this->sequence = $sequence;
        return $this;
    }

    /**
     * @throws RuntimeException
     */
    public function setStrategy(string|StrategyInterface $strategy): self
    {
        if (is_string($strategy)) {
            $tmp = @class_implements($strategy);
            if (!(is_subclass_of($strategy, StrategyInterface::class))) {
                print_r($tmp);
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
}
