<?php

declare(strict_types=1);

namespace Piper\Adapter;

use Piper\Contracts\NodeInterface;
use Piper\Contracts\ExecutableInterface;
use Piper\Contracts\StrategyInterface;
use Piper\Contracts\DataBagInterface;
use Piper\Contracts\CacheStrategy;
use Piper\Core\Event;
use Psr\Log\LoggerInterface;

final class GoogleAiTextNode implements NodeInterface
{
    private string $id;
    private ?string $template = null;
    private ?string $systemInstruction = null;
    private ?GoogleAiAdapter $adapter = null;
    private array $dependencies = [];
    private array $events = [];
    private ?LoggerInterface $logger = null;
    private ?DataBagInterface $dataBag = null;
    private ?CacheStrategy $cacheStrategy = null;
    private int $cacheTtl = 0;
    private bool $forceFresh = false;
    private ?array $outputContract = null;

    public function __construct(
        ?GoogleAiAdapter $adapter = null,
        ?string $id = null
    ) {
        $this->adapter = $adapter ?? new GoogleAiAdapter();
        $this->id = $id ?? uniqid('google_ai_text_', true);
    }

    public static function create(
        ?string $apiKey = null,
        ?string $model = null,
        ?string $voice = null,
        ?string $id = null
    ): self {
        $adapter = new GoogleAiAdapter($apiKey, $model, $voice);
        return new self($adapter, $id);
    }

    public function template(string $template): static
    {
        $this->template = $template;
        return $this;
    }

    public function systemInstruction(string $systemInstruction): static
    {
        $this->systemInstruction = $systemInstruction;
        $this->adapter->setSystemInstruction($systemInstruction);
        return $this;
    }

    public function dependsOn(ExecutableInterface $nodeOrPipeOrGraph, StrategyInterface $strategy): static
    {
        $this->dependencies[] = [$nodeOrPipeOrGraph, $strategy];
        return $this;
    }

    public function yields(\Cardinality $cardinality, \ContentType $type): static
    {
        $this->outputContract = [$cardinality, $type];
        return $this;
    }

    public function withLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function withDataBag(DataBagInterface $dataBag): static
    {
        $this->dataBag = $dataBag;
        return $this;
    }

    public function cache(CacheStrategy $strategy, int $ttlSeconds = 0): static
    {
        $this->cacheStrategy = $strategy;
        $this->cacheTtl = $ttlSeconds;
        return $this;
    }

    public function fresh(): static
    {
        $this->forceFresh = true;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function on(string $event, callable $handler): static
    {
        $this->events[$event][] = $handler;
        return $this;
    }

    public function run(mixed $input = null): mixed
    {
        $context = [
            'input' => $input,
            'dataBag' => $this->dataBag,
            'node' => $this,
        ];

        $this->trigger(Event::BEFORE_RUN, $context);

        try {
            $resolvedInput = $this->resolveInput($input);
            $result = $this->adapter->process($resolvedInput);

            $this->trigger(Event::AFTER_RUN, $context, $result);
            return $result;
        } catch (\Throwable $e) {
            $handled = false;
            if (!empty($this->events[Event::ON_ERROR])) {
                foreach ($this->events[Event::ON_ERROR] as $handler) {
                    $handler($context, $e, function () use (&$handled) { $handled = true; }, function () use (&$handled) { throw $e; });
                }
            }
            if (!$handled) {
                throw $e;
            }
            return null;
        }
    }

    private function resolveInput(mixed $input): mixed
    {
        // Template-Interpolation, DataBag, Dependency-Resolution
        $vars = is_array($input) ? $input : ['input' => $input];
        if ($this->dataBag) {
            $vars = array_merge($this->dataBag->all(), $vars);
        }
        foreach ($this->dependencies as [$dep, $strategy]) {
            $depResult = $dep->run($input);
            $vars[$dep->getId()] = $strategy->apply($depResult, fn($v) => $v);
        }
        if ($this->template) {
            return \Piper\Core\TemplateEngine::render($this->template, $vars);
        }
        return $vars['input'] ?? '';
    }

    // Convenience: Zugriff auf Adapter
    public function getAdapter(): GoogleAiAdapter
    {
