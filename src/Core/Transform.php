<?php

namespace Piper\Core;

use Piper\Contracts\TransformInterface;

final class Transform extends AbstractExecutable implements TransformInterface
{
    /** @var list<callable> */
    private array $ops = [];

    public function map(callable $fn): static
    {
        $this->ops[] = function ($x) use ($fn) {
            if (is_iterable($x)) {
                $arr = is_array($x) ? $x : iterator_to_array($x);
                return array_map($fn, $arr);
            }
            return $fn($x);
        };
        return $this;
    }

    public function filter(callable $fn): static
    {
        $this->ops[] = function ($x) use ($fn) {
            if (!is_iterable($x)) {
                return $fn($x) ? $x : null;
            }
            $arr = is_array($x) ? $x : iterator_to_array($x);
            return array_values(array_filter($arr, $fn));
        };
        return $this;
    }

    public function reduce(callable $fn, mixed $initial): static
    {
        $this->ops[] = function ($x) use ($fn, $initial) {
            $arr = is_iterable($x) ? (is_array($x) ? $x : iterator_to_array($x)) : [$x];
            return array_reduce($arr, $fn, $initial);
        };
        return $this;
    }

    public function run(mixed $input = null): mixed
    {
        $compute = function ($in) {
            $acc = $in;
            foreach ($this->ops as $op) {
                $acc = $op($acc);
            }
            return $acc;
        };

        return $this->computeWithCache($input, $compute);
    }
}
