<?php

namespace Negarity\IO;

use Negarity\Core\Image;

/**
 * WebpWriter saves Image objects as WebP files.
 * It does NOT manipulate pixels directly; it delegates to the Image's driver.
 */
final class WebpWriter implements WriterInterface
{
    /**
     * Save the Image as a WebP file to the destination.
     *
     * @param Image $image
     * @param string $destination
     * @return bool True on success, false on failure
     */
    public function save(Image $image, string $destination): bool
    {
        $driver = $image->getDriver();

        // Let the driver convert the internal resource to WebP bytes
        $webpData = $driver->extractData($image);

        if ($webpData === null) {
            return false;
        }

        // Write the binary data to the destination
        $bytesWritten = file_put_contents($destination, $webpData);

        return $bytesWritten !== false;
    }
}
