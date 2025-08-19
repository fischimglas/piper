<?php
/**
 * Example of a complex pipe that combines AI text generation, Google search,
 * file reading/writing, and filtering.
 * This example would NOT work as it is here, because the sequences / adapters are not combined
 * in a way that would continuously pass data through the pipe or use it. it is just a demonstration
 * of how sequences can be chained together and how adapters can be used.
 */

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

Pipe::create()
    // AI Text generation
    ->pipe(Sequence::create(
        adapter: GoogleAiAdapter::create(),
        template: 'Erfinde eine kurze Sci Fi Geschichte, 500 Wörter. {{storyBase}}',
        data: ['storyBase' => 'Die Geschichte soll mit Klingon beginnen und in Zürich spielen. Keine Formatierung, nur Text. '],
    ))
    // Google Search
    ->pipe(Sequence::create(
        adapter: GoogleSearchAdapter::create(),
        strategy: WholeResultStrategy::create(),
        template: 'Indisches Restaurant in Zürich Oerlikon',
        alias: 'init',
    ))
    // Stream Writer
    ->pipe(Sequence::create(
        adapter: WriteAdapter::create(
            filename: 'test.json'
        ),
        strategy: WholeResultStrategy::create(),
    ))
    // Stream Reader
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
        filter: [
            LinksInHtmlFilter::create()
        ],
    ))
    // Just filter data.
    ->pipe(Sequence::create(
        strategy: WholeResultStrategy::create(),
        filter: [
            ArrayMergeFilter::create(),
            ArrayUnique::create(),
        ],
    ));
