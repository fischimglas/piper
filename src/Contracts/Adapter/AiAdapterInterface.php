<?php

declare(strict_types=1);

namespace Piper\Contracts\Adapter;

interface AiAdapterInterface extends AdapterInterface
{
    public function process(mixed $input): mixed;

    public function setApiKey(string $apiKey): static;

    public function setModel(string $model): static;

    public function setHostUrl(string $hostUrl): static;
}
