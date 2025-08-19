<?php
declare(strict_types=1);

/**
 *
 */

use Piper\Adapter\GoogleAiAdapter;
use Piper\Core\Dependency;
use Piper\Core\Pipe;
use Piper\Core\Sequence;

require_once __DIR__ . '/vendor/autoload.php';

$ai = GoogleAiAdapter::create();

$from = new Sequence(
    adapter: new GoogleAiAdapter(),
    template: 'Invent a place in Switzerland',
);

$name = new Sequence(
    adapter: new GoogleAiAdapter(),
    template: 'Invent a name for a person',
);

$story = new Sequence(
    adapter: new GoogleAiAdapter(),
    template: 'Invent a story about {{from}}, originating from {{name}}.',
    dependencies: [
        new Dependency(sequence: $from, alias: 'from'),
        new Dependency(sequence: $name, alias: 'name'),
    ]
);

$res = Pipe::create()
    ->pipe($from)
    ->pipe($name)
    ->pipe($story)
    ->run();

print_r($res);
