<?php

namespace Piper;


use Piper\Sequence\Sequence;
use Piper\Sequence\SequenceInterface;
use RuntimeException;

class Pipe
{

    public function __construct(
        private ?string           $alias = null,
        private null|array|string $input = null,
        private array             $sequences = []
    )
    {
    }

    public static function create(?string $alias = ''): static
    {
        $element = new static();
        $element->setAlias($alias);
        return $element;
    }

    public function setAlias(?string $alias): Pipe
    {
        $this->alias = $alias;
        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setInput(string|array $it): static
    {
        $this->input = $it;
        return $this;
    }

    public function getInput(): ?string
    {
        return $this->input;
    }

    public function pipe(Sequence $sequence): static
    {
        $this->sequences[] = $sequence;
        return $this;
    }

    public function run(): mixed
    {
        return array_reduce(
            $this->sequences,
            function ($carry, Sequence $sequence) {
                $strategy = $sequence->getStrategy();

                $data = self::gatherDependencyResults($sequence);
                $sequence->setData($data);

                return $strategy->process($carry, fn($input) => $sequence->resolve($input));
            },
            $this->input
        );
    }


    private static function gatherDependencyResults(SequenceInterface $sequence): array
    {
        $results = [];
        foreach ($sequence->getDependencies() as $dep) {
            $results[$dep->getAlias()] = $dep->getResult();
        }
        return $results;
    }

    private static function topologicalSort(array $sequences): array
    {
        $sorted = [];
        $visited = [];

        $visit = function (SequenceInterface $seq) use (&$visit, &$sorted, &$visited) {
            $id = spl_object_hash($seq);

            if (isset($visited[$id])) {
                if ($visited[$id] === 'temp') {
                    throw new RuntimeException("Zyklus entdeckt bei Sequence");
                }
                return;
            }

            $visited[$id] = 'temp';

            foreach ($seq->getDependencies() as $dep) {
                $visit($dep->getSequence());
            }

            $visited[$id] = 'perm';
            $sorted[] = $seq;
        };

        foreach ($sequences as $seq) {
            $visit($seq);
        }

        return $sorted;
    }
}
