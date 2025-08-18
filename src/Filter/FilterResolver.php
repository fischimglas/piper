<?php
declare(strict_types=1);

namespace Piper\Filter;

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
                return $fmt($carry);   // jetzt wird das transformierte carry weitergereicht
            }

            if ($fmt instanceof FilterInterface) {
                return $fmt->format($carry);
            }

            throw new RuntimeException(
                "Filter must be a callable or implement FilterInterface, got: " . gettype($fmt)
            );
        }, $input);
    }

    public static function apply($input, array|string|FilterInterface $filter): mixed
    {
        $resolver = new self();
        $resolver->addFilter($filter);
        return $resolver->resolve($input);
    }
}
