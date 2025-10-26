<?php

namespace Negarity\IO\Format;

use RuntimeException;

/**
 * Class WebpFormat
 *
 * Implements FormatInterface for the WebP image format.
 */
class WebpFormat implements FormatInterface
{
    public function getName(): string
    {
        return 'webp';
    }

    public function getMimeType(): string
    {
        return 'image/webp';
    }

    public function getExtension(): string
    {
        return '.webp';
    }

    /**
     * Convert a binary string of image data into WebP.
     * Requires either Imagick or GD (with imagewebp).
     *
     * @param string $binary Input image data
     * @param int $quality 0–100
     * @return string Binary WebP data
     */
    public function convert(string $binary, int $quality = 80): string
    {
        if ($quality < 0 || $quality > 100) {
            throw new \InvalidArgumentException('Quality must be between 0 and 100.');
        }

        // Imagick preferred
        if (extension_loaded('imagick')) {
            return $this->convertWithImagick($binary, $quality);
        }

        // GD fallback
        if (function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
            return $this->convertWithGD($binary, $quality);
        }

        throw new RuntimeException('No supported image library (Imagick or GD) found for WebP conversion.');
    }

    private function convertWithImagick(string $binary, int $quality): string
    {
        $img = new \Imagick();
        $img->readImageBlob($binary);
        $img->setImageFormat('webp');
        $img->setImageCompressionQuality($quality);

        $result = $img->getImageBlob();
        $img->clear();
        $img->destroy();

        return $result;
    }

    private function convertWithGD(string $binary, int $quality): string
    {
        $src = @imagecreatefromstring($binary);
        if (!$src) {
            throw new RuntimeException('GD failed to create image from data.');
        }

        ob_start();
        imagewebp($src, null, $quality);
        imagedestroy($src);
        $data = ob_get_clean();

        if ($data === false) {
            throw new RuntimeException('GD imagewebp failed to generate output.');
        }

        return $data;
    }
}
