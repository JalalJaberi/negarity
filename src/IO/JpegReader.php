<?php

namespace Negarity\IO;

use Negarity\IO\Source\SourceInterface;

/**
 * JpegReader is responsible for reading JPEG data from a source.
 * It does NOT decode pixels or use any driver.
 */
final class JpegReader implements ReaderInterface
{
    /**
     * Load the source and return raw pixel data.
     *
     * @param SourceInterface $source
     * @return string Raw JPEG bytes
     */
    public function load(SourceInterface $source): string
    {
        $stream = $source->getStream();

        $contents = stream_get_contents($stream);
        if ($contents === false) {
            throw new \RuntimeException("Failed to read JPEG source");
        }

        return $contents;
    }
}
