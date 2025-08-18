<?php
declare(strict_types=1);

namespace Piper\Adapter;

class WriteAdapter extends AbstractAdapter implements AdapterInterface
{
    public function __construct(private string $path = '/tmp', private string $filename = 'random.txt')
    {
    }

    public static function create(string $path = '/tmp', string $filename = 'random.txt'): static
    {
        return new static($path, $filename);
    }

    public
    function process(mixed $input): mixed
    {
        file_put_contents($this->path . DIRECTORY_SEPARATOR . $this->filename, json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $input;
    }

    public function setFilename(string $filename): WriteAdapter
    {
        $this->filename = $filename;
        return $this;
    }

    public function setPath(string $path): WriteAdapter
    {
        $this->path = $path;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

}
