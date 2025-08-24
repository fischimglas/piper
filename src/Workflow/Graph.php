<?php

declare(strict_types=1);

namespace Piper\Workflow;

use Piper\Contracts\Workflow\ExecutableInterface;
use Piper\Contracts\Workflow\GraphInterface;
use Piper\Runtime\AbstractExecutable;
use Piper\Support\RunContext;

final class Graph extends AbstractExecutable implements GraphInterface
{
    /** @var list<ExecutableInterface> */
    private array $items = [];

    public function node(ExecutableInterface $nodeOrPipeOrGraph): static
    {
        $this->items[] = $nodeOrPipeOrGraph;
        return $this;
    }

    public function run(mixed $input = null): mixed
    {
        $runId = $this->runId ?? RunContext::start();
        $this->setRunId($runId);

        $compute = function (mixed $in) use ($runId) {
            // Topologische Sortierung der Dependencies
            $sorted = $this->topologicalSort();

            $results = [];
            foreach ($sorted as $ex) {
                if (method_exists($ex, 'setRunId')) {
                    $ex->setRunId($runId);
                }
                $results[$ex->getId()] = $ex->run($in);
            }
            return $results;
        };

        return $this->computeWithCache($input, $compute);
    }

    /**
     * Topologische Sortierung mit Kahn's Algorithm
     * @return list<ExecutableInterface>
     * @throws \RuntimeException bei zyklischen Abhängigkeiten
     */
    private function topologicalSort(): array
    {
        // Build dependency graph
        $inDegree = [];
        $dependencies = [];

        // Initialize all nodes
        foreach ($this->items as $item) {
            $itemId = $item->getId();
            $inDegree[$itemId] = 0;
            $dependencies[$itemId] = [];
        }

        // Build adjacency list and calculate in-degrees
        foreach ($this->items as $item) {
            $itemDeps = $this->getDependencies($item);
            foreach ($itemDeps as $dep) {
                $depId = $dep->getId();

                // Dependency must be in the graph
                if (!isset($inDegree[$depId])) {
                    throw new \RuntimeException("Dependency '{$depId}' not found in graph");
                }

                $dependencies[$depId][] = $item;
                $inDegree[$item->getId()]++;
            }
        }

        // Kahn's algorithm
        $queue = [];
        $result = [];

        // Find nodes with no incoming edges
        foreach ($inDegree as $nodeId => $degree) {
            if ($degree === 0) {
                $queue[] = $this->findNodeById($nodeId);
            }
        }

        while (!empty($queue)) {
            $current = array_shift($queue);
            $result[] = $current;

            // Remove edges from current node
            foreach ($dependencies[$current->getId()] as $dependent) {
                $inDegree[$dependent->getId()]--;

                if ($inDegree[$dependent->getId()] === 0) {
                    $queue[] = $dependent;
                }
            }
        }

        // Check for cycles
        if (count($result) !== count($this->items)) {
            throw new \RuntimeException("Cycle detected in dependency graph");
        }

        return $result;
    }

    /**
     * Extrahiert Dependencies von einem ExecutableInterface
     * @return list<ExecutableInterface>
     */
    private function getDependencies(ExecutableInterface $item): array
    {
        if (!method_exists($item, 'getDependencies')) {
            return [];
        }

        return $item->getDependencies();
    }

    /**
     * Findet Node by ID
     */
    private function findNodeById(string $id): ExecutableInterface
    {
        foreach ($this->items as $item) {
            if ($item->getId() === $id) {
                return $item;
            }
        }

        throw new \RuntimeException("Node with ID '{$id}' not found");
    }
}
