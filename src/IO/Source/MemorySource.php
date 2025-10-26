<?php

namespace Negarity\IO\Source;

use RuntimeException;

class MemorySource implements SourceInterface
{
    protected int $width;
    protected int $height;
    protected ?string $color;

    /**
     * MemorySource represents an in-memory image definition (not yet created).
     *
     * @param int $width
     * @param int $height
     * @param string|null $color
     */
    public function __construct(int $width, int $height, ?string $color = null)
    {
        $this->width = $width;
        $this->height = $height;
        $this->color = $color;
    }


    /**
     * Generate raw image data in bytes for the driver to read.
     * This can use GD or Imagick internally.
     */
    protected function generateRawImage(): string
    {
        $img = imagecreatetruecolor($this->width, $this->height);

        if ($this->color !== null) {
            [$r, $g, $b] = sscanf($this->color, '#%02x%02x%02x');
            $bg = imagecolorallocate($img, $r, $g, $b);
            imagefill($img, 0, 0, $bg);
        }

        ob_start();
        imagepng($img);
        $data = ob_get_clean();

        imagedestroy($img);

        return $data;
}

    /**
     * MemorySource has no real data stream yet — this method throws.
     */
    public function getStream(): mixed
    {
        $stream = fopen('php://memory', 'r+');
        if ($stream === false) {
            throw new \RuntimeException('Cannot create memory stream.');
        }

        // Fill the stream with a raw image representation
        // For example, a plain color JPEG or PNG data
        $imageData = $this->generateRawImage();
        fwrite($stream, $imageData);
        rewind($stream);

        return $stream;
    }

    /**
     * Unique identifier for this source (useful for caching/debugging).
     */
    public function getIdentifier(): string
    {
        return sprintf('memory:%dx%d:%s', $this->width, $this->height, $this->color ?? 'none');
    }

    /**
     * Return the source type.
     */
    public function getType(): string
    {
        return 'memory';
    }

    /**
     * Getters specific to memory-based sources
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }
}
