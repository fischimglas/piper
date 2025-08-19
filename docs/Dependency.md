# Dependency

The Dependency is required to connect sequences horizontally. (Connecting other pipes)

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
```

The Depency is connected with its own strategy, which means, streams with different data types can be connected.
The strategy defines how the data is processed and passed to the dependent sequence. 
