<?php
declare(strict_types=1);

/**
 *
 */

use Piper\Adapter\GoogleAiAdapter;
use Piper\Core\Pipe;
use Piper\Core\Sequence;

require_once __DIR__ . '/vendor/autoload.php';

$ai = GoogleAiAdapter::create();

$from = Sequence::create(
    adapter: GoogleAiAdapter::class,
    template: 'Invent a place in Switzerland',
    alias: 'from',
);

$name = Sequence::create(
    adapter: GoogleAiAdapter::class,
    template: 'Invent a name for a person',
    alias: 'name',
);

$story = Sequence::create(
    adapter: GoogleAiAdapter::class,
    template: 'Invent a story about {{from}}, originating from {{name}}.',
    alias: 'story',
    dependencies: [$from, $name],
);


// Pipe aufbauen und ausführen
$pipe = Pipe::create('main')
    ->pipe($story);

$result = $pipe->run();
print_r([$from->getResult(), $name->getResult(), $story->getResult()]);


//echo $story->resolve() . "\n";
//print_r([$from, $name]);
// print_r($res);
