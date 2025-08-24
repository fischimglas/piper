<?php

namespace Piper\Core;

use Piper\Contracts\Cardinality;
use Piper\Contracts\ContentType;

final class Foundry
{
    public static function text(string $id): Node
    {
        return (new Node($id))->yields(Cardinality::UNIT, ContentType::TEXT);
    }

    public static function image(string $id): Node
    {
        return (new Node($id))->yields(Cardinality::UNIT, ContentType::IMAGE);
    }

    public static function webSearch(string $id): Node
    {
        return (new Node($id))->yields(Cardinality::LIST, ContentType::TEXT);
    }

    public static function read(string $id): Node
    {
        return (new Node($id))->yields(Cardinality::UNIT, ContentType::TEXT);
    }

    public static function textToVoice(string $id): Node
    {
        return (new Node($id))->yields(Cardinality::UNIT, ContentType::AUDIO);
    }

    public static function transform(string $id): Transform
    {
        return new Transform($id);
    }

    public static function decide(string $id): Decider
    {
        return new Decider($id);
    }

    public static function pipe(string $id): Pipe
    {
        return new Pipe($id);
    }

    public static function graph(string $id): Graph
    {
        return new Graph($id);
    }

    public static function combine(string $id): Combine
    {
        return new Combine($id);
    }
}
