<?php

declare(strict_types=1);

namespace Piper\Core;

use Piper\Contracts\AdapterInterface;
use Piper\Contracts\FilterInterface;
use Piper\Contracts\SequenceInterface;
use Piper\Contracts\StrategyInterface;
use Piper\Strategy\WholeResultStrategy;

/**
 * Represents a Sequence in a data flow, with optional Adapter, Strategy, Template, Filters, and Dependencies.
 */
class Sequence implements SequenceInterface
{
    private mixed $result = null;
    private ?Receipt $receipt = null;

    public function __construct(
        protected AdapterInterface|string|null  $adapter = null,
        protected StrategyInterface|string|null $strategy = null,
        protected ?string                       $template = null,
        protected ?string                       $alias = null,
        protected array|FilterInterface|null    $filter = [],
        protected ?array                        $dependencies = [],
        protected ?array                        $data = [],
    )
    {
    }

    public static function create(
        AdapterInterface|string|null  $adapter = null,
        StrategyInterface|string|null $strategy = null,
        ?string                       $template = null,
        ?string                       $alias = null,
        array|FilterInterface|null    $filter = [],
        array                         $dependencies = [],
        array                         $data = []
    ): static
    {
        $el = new self(
            adapter: $adapter,
            strategy: $strategy,
            template: $template,
            alias: $alias,
            filter: $filter,
            dependencies: $dependencies,
            data: $data
        );

        $el->setAdapter($adapter);

        if (!$strategy) {
            $strategy = WholeResultStrategy::create();
        }
        $el->setStrategy($strategy);
        $el->setFilter($filter);

        if (!$el->getAlias()) {
            $el->setAlias('sequence_' . uniqid());
        }

        return $el;
    }

    public function resolve(mixed $input): mixed
    {
        $hydratedValue = $input;

        if ($this->getTemplate()) {
            $hydratedValue = TemplateResolver::resolve(
                template: $this->template,
                data: ['input' => $input],
            );
        }

        $result = $hydratedValue;

        if ($this->getAdapter()) {
            $result = $this->getAdapter()->process($hydratedValue);
        }

        if (!empty($this->getFilter())) {
            $result = FilterResolver::apply($result, $this->getFilter());
        }

        $this->getReceipt()?->log($this->getAlias(), $this->getResult());

        return $result;
    }


    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(?string $alias): static
    {
        $this->alias = $alias;
        return $this;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function setResult(mixed $result): static
    {
        $this->result = $result;
        return $this;
    }

    public function setFilter(mixed $filter): static
    {
        $this->filter = $filter;
        return $this;
    }

    public function getFilter(): mixed
    {
        return $this->filter;
    }

    public function setTemplate(?string $template): static
    {
        $this->template = $template;
        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setAdapter(AdapterInterface|string|null $adapter): static
    {
        if (is_string($adapter)) {
            $adapter = new $adapter();
        }

        $this->adapter = $adapter;
        return $this;
    }

    public function getAdapter(): ?AdapterInterface
    {
        return $this->adapter;
    }

    public function setStrategy(StrategyInterface|string|null $strategy): static
    {
        if (is_string($strategy)) {
            $strategy = new $strategy();
        }
        $this->strategy = $strategy;
        return $this;
    }

    public function getStrategy(): StrategyInterface|string|null
    {
        return $this->strategy;
    }

    public function addDependency(Dependency $dependency): static
    {
        if (!in_array($dependency, $this->dependencies, true)) {
            $this->dependencies[$dependency->getAlias()] = $dependency;
        }
        return $this;
    }

    public function setDependencies(array $dependencies): static
    {
        foreach ($dependencies as $dep) {
            $this->addDependency($dep);
        }
        return $this;
    }

    public function getDependencies(): array
    {
        return $this->dependencies ?? [];
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function touchReceipt(Receipt $receipt): static
    {
        $this->receipt = $receipt;
        return $this;
    }

    public function getReceipt(): ?Receipt
    {
        return $this->receipt;
    }
}
