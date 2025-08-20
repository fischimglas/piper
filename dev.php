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

$name = Sequence::create(
    adapter: new NoopAdapter(),
    template: 'Invent a name for a person',
    alias: 'name',
);

$res = Pipe::create('main')
    ->pipe($name)
    ->pipe($from)
    ->run();

print_r($res);

//echo $story->resolve() . "\n";
//print_r([$from, $name]);
// print_r($res);
