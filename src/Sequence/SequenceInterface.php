<?php
declare(strict_types=1);

namespace Piper\Sequence;

use Piper\Model\Dependency;

interface SequenceInterface
{
    public function addDependency(Dependency $dependency): static;

    public function getDependencies(): array;

    public function resolve(mixed $input): mixed;

    public function getResult(): mixed;

    public function setResult(mixed $result): static;


    public function getData(): array;

    public function setData(array $data): static;

}
