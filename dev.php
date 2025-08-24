<?php

use Piper\Contracts\ContentType;
use Piper\Contracts\Workflow\Cardinality;
use Piper\Factory\Foundry;
use Piper\Support\DataBag;

require_once __DIR__ . '/vendor/autoload.php';

$aiAdapter = new \Piper\Adapter\Ai\GoogleAiAdapter();
$dataBag = new DataBag();
$dataBag->set('idea', 'science fiction story about AI');

$nodeA = Foundry::text('nodeA')
    ->withDataBag($dataBag)
    ->withTemplate('Create a story about: {{idea}}')
    ->yields(Cardinality::UNIT, ContentType::TEXT);

$transform = Foundry::transform('transform')
    ->map(fn(string $s) => substr($s, 0, 250) . '...')
    ->map(fn(string $s) => strtoupper($s))
    ->yields(Cardinality::UNIT, ContentType::TEXT);

$pipe = Foundry::pipe('p')
    ->pipe($nodeA)
    ->pipe($transform);


$res = $nodeA->run('Start');

print_r($res);

//$split = Foundry::transform('split')
//    ->map(fn(string $s) => explode(' ', $s))
//    ->filter(fn($w) => strlen($w) > 3)
//    ->yields(Cardinality::LIST, ContentType::TEXT);
//
//$decide = Foundry::decide('summaryDecision')
//    ->if(fn($arr) => is_array($arr) && count($arr) > 5, $nodeA)
//    ->elseif(fn($arr) => is_array($arr) && count($arr) > 2, $split)
//    ->otherwise($nodeA);
//
//$pipe = Foundry::pipe('p')
//    ->pipe($nodeA)
//    ->pipe($split);
//
//$graph = Foundry::graph('g')
//    ->node($pipe)
//    ->node($decide);
//
//$out = $graph->run('Bern Switzerland is a beautiful city with rivers and bridges');
//
//print_r($out);
