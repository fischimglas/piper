<?php

/**
 * Experimental design for general array filtering.
 */

declare(strict_types=1);

namespace Piper\Filter;

use Piper\Contracts\FilterInterface;
use Piper\Core\AbstractFilter;

class ArrayCustomFilter extends AbstractFilter implements FilterInterface
{
    public function __construct(
        private ?bool $unique = false,
        private ?bool $trim = false,
        private ?bool $removeEmpty = false
    ) {
    }

    public static function create(
        ?bool $unique = false,
        ?bool $trim = false,
        ?bool $removeEmpty = false
    ): static {
        return new static(
            unique: $unique,
            trim: $trim,
            removeEmpty: $removeEmpty
        );
    }

    public function format(mixed $input): array
    {
        if ($this->unique) {
            $input = array_unique($input);
        }
        if ($this->trim) {
            $input = array_map('trim', $input);
        }
        if ($this->removeEmpty) {
            $input = array_values(array_filter($input));
        }

        return $input;
    }


    public function getUnique(): ?bool
    {
        return $this->unique;
    }

    public function setUnique(?bool $unique): ArrayCustomFilter
    {
        $this->unique = $unique;
        return $this;
    }

    public function getTrim(): ?bool
    {
        return $this->trim;
    }

    public function setTrim(?bool $trim): ArrayCustomFilter
    {
        $this->trim = $trim;
        return $this;
    }

    public function setRemoveEmpty(?bool $removeEmpty): ArrayCustomFilter
    {
        $this->removeEmpty = $removeEmpty;
        return $this;
    }

    public function getRemoveEmpty(): ?bool
    {
        return $this->removeEmpty;
    }
}
