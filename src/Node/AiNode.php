<?php

namespace Piper\Node;

use Piper\Adapter\Ai\GoogleAiAdapter;
use Piper\Template\TemplateEngine;

class AiNode extends Node
{
    private ?string $template;

    public function __construct(string $id)
    {
        parent::__construct($id);
        $this->withAdapter(new GoogleAiAdapter());
    }

    public function run(mixed $input = null): mixed
    {
        $data = parent::run($input);

        $prompt = $input;
        if ($this->template !== null) {
            $vars = array_merge(['input' => $input, 'self' => $this->id], $this->dataBag->all(), ['data' => $data]);

            $prompt = TemplateEngine::render($this->template, $vars);
        }

        return $this->getAdapter()->process($prompt);
    }


    public function withTemplate(?string $template): static
    {
        $this->template = $template;
        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }
}
