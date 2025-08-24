<?php

declare(strict_types=1);

namespace Piper\Node;

use Piper\Contracts\Adapter\AdapterInterface;
use Piper\Contracts\CacheStrategy;
use Piper\Contracts\ContentType;
use Piper\Contracts\DataBagInterface;
use Piper\Contracts\Node\NodeInterface;
use Piper\Contracts\Workflow\Cardinality;
use Piper\Contracts\Workflow\ExecutableInterface;
use Piper\Contracts\Workflow\StrategyInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractNode implements NodeInterface
{
    protected string $id;
    protected array $dependencies = [];
    protected array $events = [];
    protected ?LoggerInterface $logger = null;
    protected ?DataBagInterface $dataBag = null;
    protected ?CacheStrategy $cacheStrategy = null;
    protected int $cacheTtl = 0;
    protected bool $forceFresh = false;
    protected ?array $outputContract = null;
    protected ?AdapterInterface $adapter = null;
    protected mixed $result = null;

    /**
     * Kindklassen müssen diese Konstante setzen, z.B. 'text_', 'image_', etc.
     */
    protected const ID_PREFIX = 'node_';

    public function __construct(?string $id = null, ?AdapterInterface $adapter = null)
    {
        $this->id = $id ?? uniqid(static::ID_PREFIX, true);
        $this->adapter = $adapter;
    }

    public function dependsOn(ExecutableInterface $nodeOrPipeOrGraph, StrategyInterface $strategy): static
    {
        $this->dependencies[] = [$nodeOrPipeOrGraph, $strategy];
        return $this;
    }

    public function yields(Cardinality $cardinality, ContentType $type): static
    {
        $this->outputContract = [$cardinality, $type];
        return $this;
    }

    public function withLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function withDataBag(DataBagInterface $dataBag): static
    {
        $this->dataBag = $dataBag;
        return $this;
    }

    public function cache(CacheStrategy $strategy, int $ttlSeconds = 0): static
    {
        $this->cacheStrategy = $strategy;
        $this->cacheTtl = $ttlSeconds;
        return $this;
    }

    public function fresh(): static
    {
        $this->forceFresh = true;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function on(string $event, callable $handler): static
    {
        $this->events[$event][] = $handler;
        return $this;
    }

    public function withAdapter(AdapterInterface $adapter): static
    {
        $this->adapter = $adapter;
        return $this;
    }

    public function getAdapter(): ?AdapterInterface
    {
        return $this->adapter;
    }

    abstract public function run(mixed $input = null): mixed;

    public function setResult(mixed $result): AbstractNode
    {
        $this->result = $result;
        return $this;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    /**
     * @param \Piper\Contracts\Adapter\AdapterInterface|null $adapter
     * @return AbstractNode
     */
    public function setAdapter(?AdapterInterface $adapter): AbstractNode
    {
        $this->adapter = $adapter;
        return $this;
    }
}
