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
$pipe = Pipe::create()
    ->aiText(prompt: 'Invent a short sci-fi story, about 500 words.')
    ->run();

echo $pipe;
```

### Chained AI Transformations
```php
$pipe = Pipe::create()
    ->aiText(prompt: 'Invent a short sci-fi story, 500 words.')
    ->aiText(prompt: 'Rewrite the story so that it takes place in the Wild West. Story: {{input}}')
    ->run();

echo $pipe;
```

### With Translation
```php
$pipe = Pipe::create()
    ->aiText(prompt: 'Invent a short sci-fi story, 500 words.')
    ->translate(from: 'en', to: 'it')
    ->run();

echo $pipe;
```

### Using Dependencies Between Sequences
```php
$from = new TextSequence(
    adapter: new GoogleAiAdapter(),
    template: 'Invent a place in Switzerland',
);

$name = new TextSequence(
    adapter: new GoogleAiAdapter(),
    template: 'Invent a name for a person',
);

$story = new TextSequence(
    adapter: new GoogleAiAdapter(),
    dependencies: [
        new Dependency(sequence: $from, strategy: new WholeResultStrategy(), alias: 'from'),
        new Dependency(sequence: $name, strategy: new WholeResultStrategy(), alias: 'name'),
    ],
    template: 'Invent a story about {{from}}, originating from {{name}}.'
);

Pipe::run([$from, $name, $story]);

echo $story->getResult();
```

---

## Status
Currently in active development. APIs may change.
