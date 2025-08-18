<?php

use Piper\Adapter\GoogleAiAdapter;
use Piper\Adapter\GoogleSearchAdapter;
use Piper\Adapter\ReaderAdapter;
use Piper\Adapter\WriteAdapter;
use Piper\Core\Pipe;
use Piper\Core\Sequence;
use Piper\Filter\ArrayMergeFilter;
use Piper\Filter\ArrayUnique;
use Piper\Filter\JsonDecodeFilter;
use Piper\Filter\LinksInHtmlFilter;
use Piper\Strategy\PerItemStrategy;
use Piper\Strategy\WholeResultStrategy;


// Google Suche
$result = Pipe::create()
    ->pipe(Sequence::create(
        adapter: GoogleSearchAdapter::create(),
        strategy: WholeResultStrategy::create(),
        template: 'Indisches Restaurant in Zürich Oerlikon',
        alias: 'init',
    ))->run();


// ---------------------------------------------------------------------------

// CHAIN
$pipe1 = Pipe::create()
    // Das ist der initiale Input, aber das ist verführerisch. ich müsste auf die URLs unten zugreifen können.
    //->setInput(['https://secret-nature.ch', 'https://www.birosa-shop.ch'])

    //File Reader
    ->pipe(Sequence::create(
        adapter: new ReaderAdapter(filePath: __DIR__ . '/var/jam.json'),
        strategy: WholeResultStrategy::create(),
        alias: 'readFromFile',
        filter: JsonDecodeFilter::create()
    ))
    // Website Reader
    ->pipe(Sequence::create(
        adapter: new ReaderAdapter(),
        strategy: new PerItemStrategy(),
        alias: 'find',
        // Ohne template werden die daten nicht serialisiert, sondern direkt weitergegeben
        // momentan nicht optimal: Die URLs sind realtv udd sie können z.z. nicht zum original geführt werden.
        filter: [
            LinksInHtmlFilter::create()
        ],
    ))
    // FLOW STREAM / ARRAY FILTER
    // Ergebnisse zusammenführen und filter. Weitere Filter können hier hinzugefügt werden
    // es ist in einer neuen Sequenz, weil die obere eine Liste verarbeitet und die untere diese zusammenführt.
    ->pipe(Sequence::create(
        strategy: WholeResultStrategy::create(),
        filter: [
            ArrayMergeFilter::create(),
            ArrayUnique::create(),
        ],
    ))
    // File Writer
    ->pipe(Sequence::create(
        adapter: WriteAdapter::create(
            filename: 'test.json',
            path: __DIR__,
        ),
        strategy: WholeResultStrategy::create(),
    ));


// ---------------------------------------------------------------------------

// AI
$pipe2 = Pipe::create()
    ->setInput('Jam')
    ->pipe(Sequence::create(
        adapter: GoogleAiAdapter::create(),
        strategy: WholeResultStrategy::create(),
        template: 'Was ist {{input}}?',
        alias: 'init',
    ));
