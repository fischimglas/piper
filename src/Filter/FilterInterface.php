<?php
declare(strict_types=1);

namespace Piper\Filter;


interface FilterInterface
{
    public function format(mixed $input): mixed;

    public function getName(): string;

// TODO:
// In / out Trait? Format check should be done in multiple parts: filter, adapter, sequence...
//    public function getInputFormat(): DataFormat;
//    public function getOutputFormat(): DataFormat;
}
