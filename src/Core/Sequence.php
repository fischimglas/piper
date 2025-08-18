<?php
declare(strict_types=1);

namespace Piper\Core;

use Piper\Contracts\AdapterInterface;
use Piper\Contracts\FilterInterface;
use Piper\Contracts\SequenceInterface;
use Piper\Contracts\StrategyInterface;

class Sequence implements SequenceInterface
{
    private mixed $result = null;
    private Receipt|null $receipt = null;

    public function __construct(
        protected null|string|AdapterInterface  $adapter = null,
        protected null|string|StrategyInterface $strategy = null,
        protected ?string                       $template = null,
        protected ?string                       $alias = null,
        protected null|array|FilterInterface    $filter = [],
        protected null|array                    $dependencies = [],
        protected null|array                    $data = [],
    )
    {
    }

    public static function create(
        null|string|AdapterInterface  $adapter = null,
        null|string|StrategyInterface $strategy = null,
        null|string                   $template = null,
        ?string                       $alias = null,
        null|array|FilterInterface    $filter = [],
        null|array                    $dependencies = [],
        null|array                    $data = []

    ): static
    {
        $el = new self(adapter: $adapter, strategy: $strategy, template: $template, alias: $alias, filter: $filter, dependencies: $dependencies, data: $data);
        $el->setAdapter($adapter);
        $el->setStrategy($strategy);
        $el->setFilter($filter);

        if (!$el->getAlias()) {
            $el->setAlias('sequence_' . uniqid());
        }
        return $el;
    }


    public function resolve(mixed $input): mixed
    {
//        if ($this->template === null && $this->getAdapter() === null) {
//            return $input;
//        }

        // This is the part that "hydrates" the template with the data. this can be called after the strategy
        $result = $hydratedValue = $input;
        if ($this->getTemplate()) {
            $hydratedValue = TemplateResolver::resolve(
                template: $this->template,
                data: ['input' => $input],
            );
        }


        if ($this->getAdapter()) {
            $result = $this->getAdapter()->process($hydratedValue);
        }
        if (!empty($this->getFilter())) {
            $result = FilterResolver::apply($result, $this->getFilter());
        }

        // Log result
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

    private function setTemplate(null|string $template): static
    {
        $this->template = $template;
        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setAdapter(null|string|AdapterInterface $adapter): static
    {
        // TODO
        if (is_string($adapter)) {
            $adapter = new $adapter();
        }

        $this->adapter = $adapter;
        return $this;
    }

    public function getAdapter(): null|AdapterInterface
    {
        return $this->adapter;
    }

    public function setStrategy(string|StrategyInterface|null $strategy): static
    {
        if (is_string($strategy)) {
            $strategy = new $strategy();
        }
        $this->strategy = $strategy;
        return $this;
    }

    public function getStrategy(): string|StrategyInterface|null
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
        if (empty($dependencies)) {
            return $this;
        }


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
