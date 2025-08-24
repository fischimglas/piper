<?php

namespace Piper\Core;

use Piper\Contracts\CombineInterface;
use Piper\Contracts\ExecutableInterface;

final class Combine extends AbstractExecutable implements CombineInterface
{
    /** @var list<ExecutableInterface> */
    private array $execs = [];

    private ?callable $combineFn = null;
    private string $mode = 'collect'; // collect|merge|zip|custom

    public function add(ExecutableInterface $executable): static
    {
        $this->execs[] = $executable;
        return $this;
    }

    public function combineWith(callable $combineFn): static
    {
        $this->combineFn = $combineFn;
        $this->mode = 'custom';
        return $this;
    }

    public function merge(): static
    {
        $this->mode = 'merge';
        return $this;
    }

    public function zip(): static
    {
        $this->mode = 'zip';
        return $this;
    }

    public function collect(): static
    {
        $this->mode = 'collect';
        return $this;
    }

    public function run(mixed $input = null): mixed
    {
        $results = [];
        foreach ($this->execs as $ex) {
            $results[$ex->getId()] = $ex->run($input);
        }

        return match ($this->mode) {
            'merge' => array_reduce($results, fn($c, $v) => array_merge($c ?? [], is_array($v) ? $v : [$v]), []),
            'zip' => $this->zipArrays(array_values($results)),
            'custom' => ($this->combineFn)($results),
            default => $results,
        };
    }

    private function zipArrays(array $arrays): array
    {
        $max = 0;
        foreach ($arrays as $a) {
            $max = max($max, is_array($a) ? count($a) : 1);
        }
        $zipped = [];
        for ($i = 0; $i < $max; $i++) {
            $row = [];
            foreach ($arrays as $a) {
                $row[] = is_array($a) ? ($a[$i] ?? null) : $a;
            }
            $zipped[] = $row;
        }
        return $zipped;
    }
}
