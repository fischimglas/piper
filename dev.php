<?php

use Piper\Contracts\Cardinality;
use Piper\Contracts\ContentType;
use Piper\Factory\Foundry;

require_once __DIR__ . '/vendor/autoload.php';

$nodeA = Foundry::text('nodeA')
    ->template('Summarize: {{input}}')
    ->yields(Cardinality::UNIT, ContentType::TEXT);

$split = Foundry::transform('split')
    ->map(fn(string $s) => explode(' ', $s))
    ->filter(fn($w) => strlen($w) > 3)
    ->yields(Cardinality::LIST, ContentType::TEXT);

$decide = Foundry::decide('summaryDecision')
    ->if(fn($arr) => is_array($arr) && count($arr) > 5, $nodeA)
    ->elseif(fn($arr) => is_array($arr) && count($arr) > 2, $split)
    ->otherwise($nodeA);

$pipe = Foundry::pipe('p')
    ->pipe($nodeA)
    ->pipe($split);

$graph = Foundry::graph('g')
    ->node($pipe)
    ->node($decide);

$out = $graph->run('Bern Switzerland is a beautiful city with rivers and bridges');

print_r($out);
