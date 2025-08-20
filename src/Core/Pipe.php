<?php

namespace Piper\Core;

class Pipe
{
    /** @var Sequence[] */
    private array $nodes = [];
    private ?Sequence $last = null;
    private ?string $alias = null;

    public static function create(?string $alias = null): self
    {
        $pipe = new self();
        if ($alias) {
            $pipe->setAlias($alias);
        }
        return $pipe;
    }

    public function setAlias(string $alias): static
    {
        $this->alias = $alias;
        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias ?? null;
    }

    public function pipe(Sequence $sequence): self
    {
        if ($this->last) {
            $this->last->addChild($sequence);
        }
        $this->nodes[] = $sequence;
        $this->last = $sequence;
        return $this;
    }

    public function run($input = null)
    {
        $sorted = $this->topologicalSort();
        $results = [];
        $result = null;
        foreach ($sorted as $seq) {
            $depResults = ['input' => $input, 'last' => $result];
            foreach ($seq->getDependencies() as $dep) {
                $depResults[] = $dep->getResult();
            }
            foreach ($seq->getParents() as $parent) {
                $depResults[] = $parent->getResult();
            }
            /** @var \Piper\Core\Sequence $seq */
            $result = $seq->resolve($depResults ?: $input);
            $seq->setResult($result);
            $results[$seq->getAlias() ?? spl_object_hash($seq)] = $result;
        }
        return end($results);
    }

    private function topologicalSort(): array
    {
        $visited = [];
        $temp = [];
        $result = [];

        foreach ($this->nodes as $node) {
            $this->visit($node, $visited, $temp, $result);
        }
        // array_reverse entfernt!
        return $result;
    }

    private function visit(Sequence $node, array &$visited, array &$temp, array &$result)
    {
        $id = spl_object_hash($node);
        if (isset($temp[$id])) {
            throw new \RuntimeException('Zyklische Abhängigkeit erkannt');
        }
        if (!isset($visited[$id])) {
            $temp[$id] = true;
            foreach (array_merge($node->getDependencies(), $node->getParents()) as $child) {
                $this->visit($child, $visited, $temp, $result);
            }
            $visited[$id] = true;
            unset($temp[$id]);
            $result[] = $node;
        }
    }
}
