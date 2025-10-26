<?php

namespace Negarity\IO;

use Negarity\Core\Image;

/**
 * JpegWriter saves Image objects as JPEG files.
 * It does NOT manipulate pixels directly; it delegates to the Image's driver.
 */
final class JpegWriter implements WriterInterface
{
    /**
     * Save the Image as a JPEG file to the destination.
     *
     * @param Image $image
     * @param string $destination
     * @return bool True on success, false on failure
     */
    public function save(Image $image, string $destination): bool
    {
        $driver = $image->getDriver();

        // Let the driver convert the internal resource to JPEG bytes
        $jpegData = $driver->extractData($image);

        if ($jpegData === null) {
            return false;
        }

        // Write the binary data to the destination
        $bytesWritten = file_put_contents($destination, $jpegData);

        return $bytesWritten !== false;
    }
}
