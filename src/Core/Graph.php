<?php

namespace Piper\Core;

use Piper\Contracts\ExecutableInterface;
use Piper\Contracts\GraphInterface;
use Piper\Core\Support\RunContext;

final class Graph extends AbstractExecutable implements GraphInterface
{
    /** @var list<ExecutableInterface> */
    private array $items = [];

    public function node(ExecutableInterface $nodeOrPipeOrGraph): static
    {
        $this->items[] = $nodeOrPipeOrGraph;
        return $this;
    }

    public function run(mixed $input = null): mixed
    {
        $runId = $this->runId ?? RunContext::start();
        $this->setRunId($runId);

        $compute = function (mixed $in) use ($runId) {
            $results = [];
            foreach ($this->items as $ex) {
                if (method_exists($ex, 'setRunId')) {
                    $ex->setRunId($runId);
                }
                $results[$ex->getId()] = $ex->run($in);
            }
            return $results;
        };

        return $this->computeWithCache($input, $compute);
    }
}
