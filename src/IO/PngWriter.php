<?php

namespace Negarity\IO;

use Negarity\Core\Image;

/**
 * PngWriter saves Image objects as PNG files.
 * It does NOT manipulate pixels directly; it delegates to the Image's driver.
 */
final class PngWriter implements WriterInterface
{
    /**
     * Save the Image as a PNG file to the destination.
     *
     * @param Image $image
     * @param string $destination
     * @return bool True on success, false on failure
     */
    public function save(Image $image, string $destination): bool
    {
        $driver = $image->getDriver();

        // Let the driver convert the internal resource to PNG bytes
        $pngData = $driver->extractData($image);

        if ($pngData === null) {
            return false;
        }

        // Write the binary data to the destination
        $bytesWritten = file_put_contents($destination, $pngData);

        return $bytesWritten !== false;
    }
}