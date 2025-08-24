<?php
declare(strict_types=1);

namespace Piper\Core;

use Piper\Adapter\DeeplAdapter;
use Piper\Adapter\GoogleAiAdapter;
use Piper\Adapter\WriteAdapter;
use Piper\Contracts\AdapterInterface;
use Piper\Contracts\FilterInterface;
use Piper\Contracts\SequenceInterface;
use Piper\Filter\TrimFilter;
use Piper\Strategy\WholeResultStrategy;

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
        $this->addWithDependencies($sequence);
        return $this;
    }

    private function addWithDependencies(Sequence $sequence, array &$added = []): void
    {
        $id = spl_object_hash($sequence);
        if (isset($added[$id])) {
            return;
        }
        foreach ($sequence->getDependencies() as $dep) {
            $this->addWithDependencies($dep, $added);
        }
        if ($this->last) {
            $this->last->addChild($sequence);
        }
        $this->nodes[] = $sequence;
        $this->last = $sequence;
        $added[$id] = true;
    }

    public function run($input = null): SequenceInterface
    {
        $sorted = $this->topologicalSort();
        $results = [];
        $result = null;
        foreach ($sorted as $seq) {
            $depResults = ['last' => $result];
            foreach ($seq->getDependencies() as $dep) {
                $depResults[$dep->getAlias()] = $dep->getResult();
            }
            foreach ($seq->getParents() as $parent) {
                $depResults['input'] = $parent->getResult();
            }
            /** @var \Piper\Core\Sequence $seq */
            $result = $seq->resolve($input, $depResults);
            $seq->setResult($result);
            $results[$seq->getAlias() ?? spl_object_hash($seq)] = $result;
        }

        $last = end($results);

        return Sequence::create()
            ->setAlias($this->getAlias())
            ->setResult($last);
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
            // throw new \RuntimeException('Zyklische Abhängigkeit erkannt');
            return;
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

    /**
     * Predefined sequences --------------------------------------------------------------------------------
     */

    public function aiText(string $prompt, ?array $data = [], ?AdapterInterface $aiAdapter = null, ?string $alias = null): static
    {
        $sequence = Sequence::create()
            ->setTemplate($prompt)
            ->setData($data)
            ->setAdapter($aiAdapter ?: GoogleAiAdapter::create())
            ->setFilter(TrimFilter::create())
            ->setStrategy(WholeResultStrategy::class);

        if ($alias) {
            $sequence->setAlias($alias);
        }

        return $this->pipe($sequence);
    }

    public function translate(string $from, string $to, ?AdapterInterface $translateAdapter = null): static
    {
        $sequence = Sequence::create()
            ->setTemplate('{{input}}')
            ->setAdapter($translateAdapter ?: DeeplAdapter::create($from, $to))
            ->setFilter(TrimFilter::create())
            ->setStrategy(WholeResultStrategy::class);

        return $this->pipe($sequence);
    }

    public function write(string $file, ?string $path = null, ?DataFormat $dataFormat = DataFormat::STRING): static
    {
        $sequence = Sequence::create()
            // ->setTemplate('{{input}}')
            ->setAdapter(WriteAdapter::create(filename: $file, path: $path, dataFormat: $dataFormat))
            ->setStrategy(WholeResultStrategy::class);

        return $this->pipe($sequence);
    }

    public function filter(string|array|FilterInterface $filter): static
    {
        $sequence = Sequence::create()
            ->setFilter($filter)
            ->setStrategy(WholeResultStrategy::class);

        return $this->pipe($sequence);
    }
}
