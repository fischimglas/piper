<?php
declare(strict_types=1);

namespace Piper\Filter;


interface FilterInterface
{
    public function format(mixed $input): mixed;

//    public function getInputFormat(): DataFormat;
//
//    public function getOutputFormat(): DataFormat;

    public function getName(): string;
}
