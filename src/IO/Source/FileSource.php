<?php

namespace Negarity\IO\Source;

use RuntimeException;

class FileSource implements SourceInterface
{
    private string $path;

    /**
     * @param string $path Absolute or relative path to the image file.
     * @throws RuntimeException if file does not exist or is not readable
     */
    public function __construct(string $path)
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException("File not found or not readable: $path");
        }

        $this->path = $path;
    }

    /**
     * Returns a readable PHP stream for this file.
     * @throws RuntimeException if the file cannot be opened
     * @return resource
     */
    public function getStream(): mixed
    {
        $handle = fopen($this->path, 'rb');
        if (!$handle) {
            throw new RuntimeException("Failed to open file stream: {$this->path}");
        }
        return $handle;
    }

    /**
     * Returns a string identifier (the file path).
     */
    public function getIdentifier(): string
    {
        return $this->path;
    }

    /**
     * Returns the source type: 'file'
     */
    public function getType(): string
    {
        return 'file';
    }
}
