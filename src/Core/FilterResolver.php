<?php
/**
 * Autoloads the required classes and runs the filter serialized.
 *
 * Usage:
 * $resolver = new FilterResolver();
 * $resolver->addFilter($filter);
 * ...
 * $result = $resolver->resolve($input);
 */
declare(strict_types=1);

namespace Piper\Core;

use Piper\Contracts\FilterInterface;
use RuntimeException;

class FilterResolver
{
    private array $filter = [];

    public function addFilter(array|string|FilterInterface $filter): static
    {
        if (is_array($filter)) {
            foreach ($filter as $f) {
                $this->addFilter($f);
            }
            return $this;
        }
        if (is_callable($filter)) {
            $this->filter[] = $filter;
            return $this;
        }

        if ($filter instanceof FilterInterface) {
            $this->filter[] = $filter;
            return $this;
        }

        if (is_string($filter) && class_exists($filter) && is_subclass_of($filter, FilterInterface::class)) {
            $this->filter[] = new $filter();
            return $this;
        }

        throw new RuntimeException(
            "Filter must be a callable or implement FormatterInterface, got: " . gettype($filter)
        );
    }

    public function resolve($input): mixed
    {
        return array_reduce($this->filter, function ($carry, $fmt) {
            if (is_callable($fmt)) {
                return $fmt($carry);
            }

            if ($fmt instanceof FilterInterface) {
                return $fmt->format($carry);
            }

            throw new RuntimeException(sprintf("Filter must be a callable or implement FilterInterface, got: %s", gettype($fmt)));
        }, $input);
    }

    public static function apply($input, array|string|FilterInterface $filter): mixed
    {
        $resolver = new self();
        return $resolver
            ->addFilter($filter)
            ->resolve($input);
    }
}
