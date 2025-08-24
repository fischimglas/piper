<?php
declare(strict_types=1);

namespace Piper\Workflow;

use Piper\Contracts\DeciderInterface;
use Piper\Contracts\ExecutableInterface;
use Piper\Contracts\StrategyInterface;
use Piper\Runtime\AbstractExecutable;

final class Decider extends AbstractExecutable implements DeciderInterface
{
    /** @var array<int, array{cond:callable,target:ExecutableInterface}> */
    private array $conditions = [];
    private ?ExecutableInterface $otherwise = null;

    // erfüllt auch NodeInterface (via DeciderInterface extends NodeInterface)
    public function dependsOn(ExecutableInterface $nodeOrPipeOrGraph, StrategyInterface $strategy): static
    {
        // Decider nutzt i.d.R. keine dependsOn; Stub für Interface-Konformität.
        return $this;
    }

    public function if(callable $condition, ExecutableInterface $target): static
    {
        $this->conditions[] = ['cond' => $condition, 'target' => $target];
        return $this;
    }

    public function elseif(callable $condition, ExecutableInterface $target): static
    {
        return $this->if($condition, $target);
    }

    public function otherwise(ExecutableInterface $target): static
    {
        $this->otherwise = $target;
        return $this;
    }

    public function run(mixed $input = null): mixed
    {
        foreach ($this->conditions as $entry) {
            if (($entry['cond'])($input)) {
                return $entry['target']->run($input);
            }
        }
        return $this->otherwise?->run($input);
    }
}
