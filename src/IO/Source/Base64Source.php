<?php

namespace Negarity\IO\Source;

use InvalidArgumentException;
use RuntimeException;

/**
 * Class Base64Source
 *
 * Represents a data source backed by a Base64 string or data URI.
 * Implements SourceInterface and provides a stream handle for reading.
 */
class Base64Source implements SourceInterface
{
    private string $base64;
    private string $identifier;
    private string $mimeType;

    /**
     * @param string $base64OrDataUri Raw base64 data or full data URI
     * @param string|null $identifier Optional identifier (hash, label, etc.)
     */
    public function __construct(string $base64OrDataUri, ?string $identifier = null)
    {
        // Extract MIME and pure base64 part
        if (preg_match('#^data:([\w/+.-]+);base64,(.*)$#is', $base64OrDataUri, $m)) {
            $this->mimeType = strtolower(trim($m[1]));
            $this->base64 = $m[2];
            $this->identifier = $identifier ?? md5($this->base64);
        } else {
            $this->mimeType = 'application/octet-stream';
            $this->base64 = $base64OrDataUri;
            $this->identifier = $identifier ?? md5($this->base64);
        }

        // Validate base64
        if (base64_decode($this->base64, true) === false) {
            throw new InvalidArgumentException('Invalid base64 data provided.');
        }
    }

    /**
     * Returns a readable stream resource handle.
     *
     * @return resource
     */
    public function getStream(): mixed
    {
        $binary = base64_decode($this->base64, true);
        if ($binary === false) {
            throw new RuntimeException('Failed to decode base64 data.');
        }

        $stream = fopen('php://temp', 'rb+');
        if ($stream === false) {
            throw new RuntimeException('Failed to open temporary stream.');
        }

        fwrite($stream, $binary);
        rewind($stream);
        return $stream;
    }

    /**
     * Identifier for this source (e.g. hash or label).
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Type label: always "base64".
     */
    public function getType(): string
    {
        return 'base64';
    }

    /**
     * MIME type of decoded data.
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Returns decoded binary data as a string.
     */
    public function getContents(): string
    {
        $binary = base64_decode($this->base64, true);
        if ($binary === false) {
            throw new RuntimeException('Failed to decode base64 content.');
        }
        return $binary;
    }
}
