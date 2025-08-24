<?php

declare(strict_types=1);

namespace Piper\Core;

use Piper\Contracts\ExecutableInterface;
use Piper\Contracts\PipeInterface;
use Piper\Core\Support\RunContext;

final class Pipe extends AbstractExecutable implements PipeInterface
{
    /** @var list<ExecutableInterface> */
    private array $elements = [];
    private mixed $inputData = null;

    public function input(array $data): static
    {
        $this->inputData = $data;
        return $this;
    }

    public function pipe(ExecutableInterface $nodeOrPipe): static
    {
        $this->elements[] = $nodeOrPipe;
        return $this;
    }

    public function run(mixed $input = null): mixed
    {
        $runId = $this->runId ?? RunContext::current() ?? RunContext::start();
        $this->setRunId($runId);

        $compute = function (mixed $in) use ($runId) {
            $acc = $in;
            foreach ($this->elements as $el) {
                if (method_exists($el, 'setRunId')) {
                    $el->setRunId($runId);
                }
                $acc = $el->run($acc);
            }
            return $acc;
        };

        $start = $input ?? $this->inputData;
        return $this->computeWithCache($start, $compute);
    }
}
