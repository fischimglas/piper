# Piper

Status: Working, dev

The Piper is the "natural" flow of the data. It connects sequences vertically, allowing you to create a pipeline of
operations that can be executed in sequence. Each sequence can consume input, process data, and produce results.

Only sequences can be "piped", but they don't need to contain modifiers, data can also just be passed through. For
an easier use, the Pipe knows multiple shortcuts to reduce the boilerplate.

```php
$result = Pipe::create()
    ->aiText(prompt: 'Invent a short sci-fi story, 500 words.')
    ->translate(from: 'en', to: 'it')
    ->run();

echo $result;
```

This code equals to:

```php  
$result = Pipe::create()
    ->sequence(new TextSequence(
        adapter: new GoogleAiAdapter(),
        template: 'Invent a short sci-fi story, 500 words.'
    ))
    ->sequence(new TranslateSequence(
        adapter: new DeeplAdapter(),
        from: 'en',
        to: 'it'
    ))
    ->run();
echo $result;
```

### Automatic Dependency Resolution

The Piper will automatically decide which sequence is resolved first,
based on the dependencies and the order of the sequences, which means,
even if a pipe is created first, it will be executed after a dependency is resolved.
