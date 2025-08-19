<?php

/**
 * Run Pipes with sequences and dependencies.
 *
 * Usage:
 * $pipe = Pipe::create()
 *            ->setInput('initial data')
 *           ->pipe(new Sequence())
 *           ->pipe(new Sequence());
 * * $result = $pipe->run();
 */

declare(strict_types=1);

namespace Piper\Core;

use Piper\Adapter\DeeplAdapter;
use Piper\Adapter\GoogleAiAdapter;
use Piper\Adapter\GoogleSearchAdapter;
use Piper\Contracts\AdapterInterface;
use Piper\Contracts\FilterInterface;
use Piper\Contracts\SequenceInterface;
use Piper\Strategy\WholeResultStrategy;
use RuntimeException;

class Pipe
{
    private Receipt $receipt;

    public function __construct(
        private ?string $alias = null,
        private null|array|string $input = null,
        private array $sequences = []
    ) {
        $this->receipt = new Receipt();
    }

    public static function create(
        ?string $alias = null,
    ): static {
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

    // TODO: should be removed
    public function setInput(string|array $it): static
    {
        $this->input = $it;
        return $this;
    }

    // TODO: should be removed
    public function getInput(): ?string
    {
        return $this->input;
    }

    public function pipe(Sequence $sequence): static
    {
        $this->sequences[] = $sequence;
        return $this;
    }

    /**
     * TODO:
     * the return value should remain mixed for the moment,
     * As it is not decided yet if the Pipe will return other types as well.
     */
    public function run(): mixed
    {
        return array_reduce(
            $this->sequences,
            function ($carry, Sequence $sequence) {
                $strategy = $sequence->getStrategy();

                $data = self::gatherDependencyResults($sequence);
                $sequence->setData($data);
                $sequence->touchReceipt($this->receipt);

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

    /**
     * TODO:
     * this will be required to ensure that the sequences are executed in the correct order.
     */
    private static function topologicalSort(array $sequences): array
    {
        $sorted = [];
        $visited = [];

        $visit = function (SequenceInterface $seq) use (&$visit, &$sorted, &$visited) {
            $id = spl_object_hash($seq);

            if (isset($visited[$id])) {
                if ($visited[$id] === 'temp') {
                    throw new RuntimeException("Circular dependency detected in sequences.");
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

    public function getReceipt(): Receipt
    {
        return $this->receipt;
    }


    /**
     * Predefined sequences --------------------------------------------------------------------------------
     */

    public function aiText(string $prompt, ?array $data = [], ?AdapterInterface $aiAdapter = null): static
    {
        $sequence = Sequence::create()
            ->setTemplate($prompt)
            ->setData($data)
            ->setAdapter($aiAdapter ?: GoogleAiAdapter::create())
            ->setStrategy(WholeResultStrategy::class);

        return $this->pipe($sequence);
    }

    public function translate(string $from, string $to, ?AdapterInterface $translateAdapter = null): static
    {
        $sequence = Sequence::create()
            ->setAdapter($translateAdapter ?: DeeplAdapter::create($from, $to))
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

    public function search(string $searchFor = ''): static
    {
        $sequence = Sequence::create()
            ->setTemplate($searchFor)
            ->setAdapter(GoogleSearchAdapter::create())
            ->setStrategy(WholeResultStrategy::class);

        return $this->pipe($sequence);
    }
}
