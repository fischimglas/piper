<?php

namespace Piper\Contracts;

use Psr\Log\LoggerInterface;

interface ExecutableInterface
{
    public function run(mixed $input = null): mixed;

    public function yields(Cardinality $cardinality, ContentType $type): static;

    public function withLogger(LoggerInterface $logger): static;

    public function getId(): string;

    public function withDataBag(DataBagInterface $dataBag): static;

    public function cache(CacheStrategy $strategy, int $ttlSeconds = 0): static;

    public function fresh(): static;
}
