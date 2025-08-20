# Piper

**State: Development!**

Piper is a lightweight PHP framework for building AI- and API-driven pipelines. It enables the orchestration of sequences of operations with **adapters**, **strategies**, and **dependencies**. This allows you to create **complicated data flows**, where the output of one sequence can be reused or transformed by another.

## Features
- Define sequences of operations that can consume input, process data, and produce results.
- Support for dependencies between sequences, with configurable strategies for how to pass results.
- Template-based text resolution, with dynamic placeholders filled from upstream results.
- Extendable with custom adapters, strategies, and filters.

## Available Adapters
- **GoogleAiAdapter** – interface for Google AI models.
- **OpenAiAdapter** – interface for OpenAI models (ChatGPT, GPT, etc.).
- **DeeplAdapter** – translation with DeepL.
- **GoogleSearchAdapter** – integration with Google Search API.

## Examples

### Simple AI Text Generation
```php
echo Pipe::create()
    ->aiText(prompt: 'Invent a short sci-fi story, about 500 words.')
    ->run()
    ->getResult();
```

### Chained AI Transformations
```php
echo Pipe::create()
    ->aiText(prompt: 'Invent a short sci-fi story, 500 words.')
    ->aiText(prompt: 'Rewrite the story so that it takes place in the Wild West. Story: {{input}}')
    ->run()
    ->getResult();
```

### With Translation
```php
echo Pipe::create()
    ->aiText(prompt: 'Invent a short sci-fi story, 500 words.')
    ->translate(from: 'en', to: 'it')
    ->run()
    ->getResult();
```

### Using Dependencies Between Sequences

Dependency management is currently under development. The following example illustrates the intended usage, but the feature is not yet fully implemented.
```php

$from = Sequence::create(
    adapter: GoogleAiAdapter::class,
    template: 'Invent a place in Switzerland. Return only the name of the place, No other text.',
    alias: 'from',
    filter: TrimFilter::create(),
);

$name = Sequence::create(
    adapter: GoogleAiAdapter::class,
    template: 'Invent a name for a person. Return only the name, no other text.',
    alias: 'name',
    filter: TrimFilter::create(),
);

$story = Sequence::create(
    adapter: GoogleAiAdapter::class,
    template: 'Invent a story about {{from}}, originating from {{name}}.',
    alias: 'story',
    dependencies: [$from, $name],
);


$res = Pipe::create('main')
    ->pipe($story)
    ->translate('en', 'de')
    ->run();

print_r([
    'name' => $name->getResult(),
    'story, german' => $res->getResult()
]);

```

---

## Status
Currently in active development. APIs may change.
