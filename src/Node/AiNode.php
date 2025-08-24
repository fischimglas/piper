<?php

declare(strict_types=1);

namespace Piper\Node;

use InvalidArgumentException;
use Piper\Contracts\Adapter\AdapterInterface;
use Piper\Contracts\Adapter\AiAdapterInterface;
use Piper\Contracts\Node\AiNodeInterface;
use Piper\Template\TemplateEngine;

class AiNode extends AbstractNode implements AiNodeInterface
{
    protected const ID_PREFIX = 'ai_';

    // protected ?string $template = null;
    private ?string $template = null;

    public function run(mixed $input = null): mixed
    {
        $templateData = [];
        $prompt = TemplateEngine::render(
            template: $this->template,
            vars: $templateData,
        );
        $result = $this->getAdapter()->process($prompt);

        $this->setResult($result);

        return $result;
    }

    public function getAdapter(): ?AiAdapterInterface
    {
        /** @var AiAdapterInterface $this ->adapter */
        return $this->adapter;
    }

    public function withAdapter(AdapterInterface $adapter): static
    {
        if (!$adapter instanceof AiAdapterInterface) {
            throw new InvalidArgumentException('Der Adapter muss eine Instanz von AiAdapterInterface sein.');
        }
        $this->adapter = $adapter;
        return $this;
    }

    public function setTemplate(?string $template): static
    {
        $this->template = $template;
        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }
}
