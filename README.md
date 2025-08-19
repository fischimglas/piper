# Piper

Piper is a flexible PHP library for processing and transforming data using adapters, filters, and strategies. It allows
you to easily define and extend complex processing chains (pipelines).

## Features

- Adapters for various data sources and targets (e.g., Deepl, Google AI, Google Search)
- Filters for data transformation and validation (e.g., array operations, JSON parsing)
- Strategies to control how processing is applied (e.g., per item, whole result)
- Easily extendable with your own adapters, filters, and strategies

## Installation

Install via Composer:

```bash
composer require fischimglas/piper
```

## Core Concepts

Piper works with three main components:

- **Adapter**: Interfaces to external services or data sources
- **Filter**: Transformations or checks on the data
- **Strategy**: Controls how filters are applied to the data

## Example

The following example shows how to build a pipeline with multiple filters and adapters:

```php
<?php

use Piper\Core\Pipe;

$pipe = Pipe::create()
    ->aiText(prompt: 'Create a short Sci-Fi Story, 500 Wörter.')
    ->aiText(prompt: 'Rewrite the story, so it play in the wild west. Story: {{input}}.')
    ->translate(from: 'en', to: 'fr');
```

### Multiple Adapters

```php
<?php


$pipe = Pipe::create()
    ->search(searchFor: 'Fondue Restaurant in Zurich')
    ->filter('array', 'map', fn ($item)  => ['name' => $item['title'],'url' => $item['link'],]})
    ->readUrls()
    ->aiText(prompt: 'Find all important contact data like name of place, phone, email, etc. from the HTML: {{input}}')
    ->translate(from: 'en', to: 'fr')
    ->write('output.json');
```

### Dependencies

```php
<?php

$pipe1 = Pipe::create('myAlias')
    ->search(searchFor: 'Fondue Restaurant in Zurich')
    ->readUrls();
 
$pipe2 = Pipe::create()
    ->aiText(prompt: 'Find something in those URLS: {{myAlias}}', dependsOn: $pipe1)
    ->write('output.txt');
```

## Documentation

See the [Docs](docs/) for more details:

- [Adapter](docs/Adapter.md)
- [Filter](docs/Filter.md)
- [Strategies](docs/Piper.md)
- [Example](docs/example.php)
- [Templates](docs/Templates.md)
- [Events](docs/Events.md)
- [Receipt](docs/Receipt.md)
- [Dependency](docs/Dependency.md)
- [Sequence](docs/Sequence.md)

## License

WTFPL

---

Done so with ❤️ by Jam.
