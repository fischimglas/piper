<?php
declare(strict_types=1);

/**
 *
 */

use Piper\Adapter\GoogleAiAdapter;
use Piper\Adapter\NoopAdapter;
use Piper\Core\Pipe;
use Piper\Core\Sequence;

require_once __DIR__ . '/vendor/autoload.php';

$ai = GoogleAiAdapter::create();

$from = Sequence::create(
    adapter: new NoopAdapter(),
    template: 'Invent a place in Switzerland',
    alias: 'from',
);

// Sequenzen anlegen
$from = Sequence::create(
    adapter: new NoopAdapter(),
    template: 'Invent a place in Switzerland',
    alias: 'from',
);

$name = Sequence::create(
    adapter: new NoopAdapter(),
    template: 'Invent a name for a person',
    alias: 'name',
);

$story = Sequence::create(
    adapter: new GoogleAiAdapter(),
    template: 'Invent a story about {{from}}, originating from {{name}}.',
    alias: 'story',
);

$story->dependsOn($from)
    ->dependsOn($name);

// Pipe aufbauen und ausführen
$res = Pipe::create('main')
    ->pipe($name)
    ->pipe($from)
    ->pipe($story)
    ->run();


print_r($res);

//echo $story->resolve() . "\n";
//print_r([$from, $name]);
// print_r($res);
