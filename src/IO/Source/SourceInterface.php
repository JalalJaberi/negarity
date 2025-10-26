<?php

namespace Negarity\IO\Source;

interface SourceInterface
{
    public function getStream(): mixed;       // Returns a stream/resource handle
    public function getIdentifier(): string;  // File path, URL, hash, etc.
    public function getType(): string;        // e.g. "file", "base64", "url"
}
