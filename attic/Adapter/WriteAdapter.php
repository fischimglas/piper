<?php

declare(strict_types=1);

namespace Piper\Adapter;

use Piper\Contracts\AdapterInterface;
use Piper\Core\Cf;
use Piper\Core\DataFormat;

class WriteAdapter implements AdapterInterface
{
    private string $filename;
    private string $path;

    public function __construct(
        string             $filename,
        ?string            $path = null,
        private DataFormat $format = DataFormat::JSON,
    )
    {
        Cf::autoload($this);
        if ($filename) {
            $this->filename = $filename;
        }
        if ($path) {
            $this->path = $path;
        }
    }

    public static function create(
        string      $filename,
        ?string     $path = null,
        ?DataFormat $dataFormat = DataFormat::JSON,
    ): static
    {
        return new static(filename: $filename, path: $path, format: $dataFormat);
    }

    public function process(mixed $input): mixed
    {
        $path = $this->path . DIRECTORY_SEPARATOR . $this->filename;
        echo "Writing to file: $path\n";
        file_put_contents($path, json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

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
