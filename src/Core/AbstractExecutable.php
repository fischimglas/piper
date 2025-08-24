<?php

declare(strict_types=1);

namespace Piper\Core;

use Piper\Contracts\CacheInterface;
use Piper\Contracts\CacheStrategy;
use Piper\Contracts\Cardinality;
use Piper\Contracts\ContentType;
use Piper\Contracts\DataBagInterface;
use Piper\Contracts\ExecutableInterface;
use Piper\Core\Cache\InMemoryCache;
use Piper\Core\Support\RunContext;
use Psr\Log\LoggerInterface;

abstract class AbstractExecutable implements ExecutableInterface
{
    protected string $id;
    protected ?LoggerInterface $logger = null;
    protected ?Cardinality $cardinality = null;
    protected ?ContentType $contentType = null;
    protected ?DataBagInterface $dataBag = null;

    protected CacheStrategy $cacheStrategy = CacheStrategy::DISABLED;
    protected int $ttlSeconds = 0;
    protected bool $forceFresh = false;
    protected CacheInterface $cache;
    protected ?string $runId = null;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->cache = new InMemoryCache();
    }

    public function yields(Cardinality $cardinality, ContentType $type): static
    {
        $this->cardinality = $cardinality;
        $this->contentType = $type;
        return $this;
    }

    public function withLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function withDataBag(DataBagInterface $dataBag): static
    {
        $this->dataBag = $dataBag;
        return $this;
    }

    public function cache(CacheStrategy $strategy, int $ttlSeconds = 0): static
    {
        $this->cacheStrategy = $strategy;
        $this->ttlSeconds = $ttlSeconds;
        return $this;
    }

    public function fresh(): static
    {
        $this->forceFresh = true;
        return $this;
    }

    public function withCache(CacheInterface $cache): static
    {
        $this->cache = $cache;
        return $this;
    }

    public function setRunId(string $runId): void
    {
        $this->runId = $runId;
    }

    protected function computeWithCache(mixed $input, callable $compute): mixed
    {
        $key = $this->makeCacheKey($input);

        if ($this->forceFresh || $this->cacheStrategy === CacheStrategy::DISABLED || $key === null) {
            return $compute($input);
        }

        try {
            if ($this->cache->has($key)) {
                return $this->cache->get($key);
            }
        } catch (\Throwable $e) {
            $this->logger?->error('Cache-Fehler: ' . $e->getMessage(), ['key' => $key]);
        }

        $result = $compute($input);

        try {
            $this->cache->set($key, $result, $this->ttlSeconds);
        } catch (\Throwable $e) {
            $this->logger?->error('Cache-Fehler: ' . $e->getMessage(), ['key' => $key]);
        }

        return $result;
    }

    private function makeCacheKey(mixed $input): ?string
    {
        return match ($this->cacheStrategy) {
            CacheStrategy::DISABLED => null,
            CacheStrategy::PER_RUN => 'run:' . ($this->runId ?? RunContext::current() ?? 'none') . ':' . $this->id,
            CacheStrategy::PER_INPUT => 'input:' . $this->id . ':' . hash('xxh128', serialize($input)),
            CacheStrategy::GLOBAL => 'global:' . $this->id,
        };
    }
}
