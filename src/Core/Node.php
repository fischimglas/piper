<?php

declare(strict_types=1);

namespace Piper\Core;

use Piper\Contracts\ExecutableInterface;
use Piper\Contracts\NodeInterface;
use Piper\Contracts\StrategyInterface;
use Piper\Core\Support\RunContext;

final class Node extends AbstractExecutable implements NodeInterface
{
    /** @var array<int, array{dep:ExecutableInterface,strategy:StrategyInterface}> */
    private array $deps = [];
    private ?string $template = null;

    /** @var array<string, list<callable>> */
    private array $handlers = [];

    public function __construct(string $id)
    {
        parent::__construct($id);

        // Initialize handlers array with Event values
        $this->handlers[Event::BEFORE_RUN->name] = [];
        $this->handlers[Event::AFTER_RUN->name] = [];
        $this->handlers[Event::ON_ERROR->name] = [];
    }

    public function template(string $template): static
    {
        $this->template = $template;
        return $this;
    }

    public function dependsOn(ExecutableInterface $nodeOrPipeOrGraph, StrategyInterface $strategy): static
    {
        $this->deps[] = ['dep' => $nodeOrPipeOrGraph, 'strategy' => $strategy];
        return $this;
    }

    public function on(Event $event, callable $listener): static
    {
        $this->handlers[$event->name][] = $listener;
        return $this;
    }

    public function run(mixed $input = null): mixed
    {
        $runId = $this->runId ?? RunContext::current() ?? RunContext::start();
        $this->setRunId($runId);

        $this->emit(Event::BEFORE_RUN, [$this]);

        $compute = function (mixed $in) use ($runId) {
            $resolved = [];
            foreach ($this->deps as $entry) {
                $dep = $entry['dep'];
                if (method_exists($dep, 'setRunId')) {
                    $dep->setRunId($runId);
                }
                $out = $entry['strategy']->apply($in, fn($x) => $dep->run($x));
                $resolved[$dep->getId()] = $out;
            }

            if ($this->template !== null) {
                $vars = array_merge(['input' => $in, 'self' => $this->id], $resolved);
                return TemplateEngine::render($this->template, $vars);
            }

            // Default: gebe Input oder zusammengesetzte Abhängigkeiten zurück
            return empty($resolved) ? $in : $resolved;
        };

        try {
            $result = $this->computeWithCache($input, $compute);
            $this->emit(Event::AFTER_RUN, [$this, $result]);
            return $result;
        } catch (\Throwable $e) {
            $decision = 'throw';
            $this->emit(Event::ON_ERROR, [$this, $e, function () use (&$decision) {
                $decision = 'continue';
            }, function () use (&$decision) {
                $decision = 'throw';
            }]);
            if ($decision === 'continue') {
                return null;
            }
            throw $e;
        }
    }

    public function getDependencies(): array
    {
        return array_map(fn($entry) => $entry['dep'], $this->deps);
    }

    private function emit(Event $event, array $args): void
    {
        foreach ($this->handlers[$event->name] as $listener) {
            $listener(...$args);
        }
    }
}
