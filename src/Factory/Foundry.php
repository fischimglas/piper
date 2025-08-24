<?php
declare(strict_types=1);

namespace Piper\Factory;

use Piper\Contracts\CombineInterface;
use Piper\Contracts\ContentType;
use Piper\Contracts\Node\AiNodeInterface;
use Piper\Contracts\Node\NodeInterface;
use Piper\Contracts\Workflow\Cardinality;
use Piper\Contracts\Workflow\DeciderInterface;
use Piper\Contracts\Workflow\GraphInterface;
use Piper\Contracts\Workflow\PipeInterface;
use Piper\Contracts\Workflow\TransformInterface;
use Piper\Node\AiNode;
use Piper\Node\ImageNode;
use Piper\Node\Node;
use Piper\Node\TextToVoiceNode;
use Piper\Node\WebSearchNode;
use Piper\Workflow\Combine;
use Piper\Workflow\Decider;
use Piper\Workflow\Graph;
use Piper\Workflow\Pipe;
use Piper\Workflow\Transform;

final class Foundry
{
    public static function text(string $id): AiNodeInterface
    {
        return (new AiNode($id))->yields(Cardinality::UNIT, ContentType::TEXT);
    }

    public static function image(string $id): NodeInterface
    {
        return (new ImageNode($id))->yields(Cardinality::UNIT, ContentType::IMAGE);
    }

    public static function webSearch(string $id): NodeInterface
    {
        return (new WebSearchNode($id))->yields(Cardinality::LIST, ContentType::TEXT);
    }

    public static function read(string $id): NodeInterface
    {
        return (new Node($id))->yields(Cardinality::UNIT, ContentType::TEXT);
    }

    public static function textToVoice(string $id): NodeInterface
    {
        return (new TextToVoiceNode($id))->yields(Cardinality::UNIT, ContentType::AUDIO);
    }

    public static function transform(string $id): TransformInterface
    {
        return new Transform($id);
    }

    public static function decide(string $id): DeciderInterface
    {
        return new Decider($id);
    }

    public static function pipe(string $id): PipeInterface
    {
        return new Pipe($id);
    }

    public static function graph(string $id): GraphInterface
    {
        return new Graph($id);
    }

    public static function combine(string $id): CombineInterface
    {
        return new Combine($id);
    }
}
