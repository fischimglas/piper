# Receipt

Status: Working, dev

Through the Pipe, a receipt is passed along, which contains all the information about the sequences,
You can access it via the `getReceipt()` method.

```php
$pipe = Pipe::create()
    ->aiText(prompt: 'Your AI Prompt.')
    ->run();

$receipt = $pipe->getReceipt();

```
