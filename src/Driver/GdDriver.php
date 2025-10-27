<?php

namespace Negarity\Driver;

use Negarity\Core\Image;
use Negarity\IO\Source\{
    SourceInterface,
    FileSource,
    Base64Source,
    MemorySource
};
use Negarity\IO\Format\FormatInterface;
use RuntimeException;

class GdDriver extends AbstractDriver
{
    public function __construct()
    {
        parent::__construct('gd');
    }

    public function isAvailable(): bool
    {
        return extension_loaded('gd');
    }

    public function getWidth(mixed $resource): int
    {
        return imagesx($resource);
    }

    public function getHeight(mixed $resource): int
    {
        return imagesy($resource);
    }

    public function createResource(mixed $rawPixels): mixed
    {
        return imagecreatefromstring($rawPixels);
    }

    public function createImageFromSource(SourceInterface $source): Image
    {
        // MemorySource → create blank image
        if ($source instanceof MemorySource) {
            $width = $source->getWidth();
            $height = $source->getHeight();
            $img = imagecreatetruecolor($width, $height);

            if ($color = $source->getColor()) {
                $rgb = sscanf($color, "#%02x%02x%02x");
                $fill = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
                imagefill($img, 0, 0, $fill);
            }

            return new Image($img, $width, $height, 'png', $this);
        }

        // FileSource or Base64Source → load image data
        if ($source instanceof FileSource) {
            $data = file_get_contents($source->getIdentifier());
            $format = strtolower(pathinfo($source->getIdentifier(), PATHINFO_EXTENSION));
        } elseif ($source instanceof Base64Source) {
            $data = base64_decode($source->getData());
            $format = 'png';
        } else {
            throw new RuntimeException('Unsupported source for GD driver.');
        }

        if (!$data) {
            throw new RuntimeException('Failed to read image data.');
        }

        $img = @imagecreatefromstring($data);
        if (!$img) {
            throw new RuntimeException('GD failed to create image from source.');
        }

        $width = imagesx($img);
        $height = imagesy($img);

        return new Image($img, $width, $height, $format, $this);
    }

    public function extractData(Image $image): mixed
    {
        $format = $image->getFormat();
        if ($format instanceof FormatInterface) {
            $format = $format->getName();
        }

        ob_start();

        switch (strtolower($format)) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($image->getDriverResource());
                break;
            case 'png':
                imagepng($image->getDriverResource());
                break;
            case 'gif':
                imagegif($image->getDriverResource());
                break;
            default:
                throw new \RuntimeException("Unsupported format '{$format}' for GD driver.");
        }

        return ob_get_clean();
    }


    /**
     * Allocate a color for a GD image resource from a hex string.
     *
     * Supports:
     *  - '#RRGGBB'
     *  - '#RRGGBBAA' where AA is alpha in hex (00 = opaque, FF = fully transparent)
     *
     * Returns the GD color index (int) for use with drawing functions.
     *
     * @param resource|\GdImage $resource
     * @param string $hex
     * @return int
     */
    private function allocateColor($resource, string $hex): int
    {
        $hex = trim($hex);

        // Support short forms like '#fff' or '#ffff' if you want — currently expect full hex
        if (strpos($hex, '#') === 0) {
            $hex = substr($hex, 1);
        }

        // Normalize length
        if (strlen($hex) === 3) { // 'rgb' -> 'rrggbb'
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        } elseif (strlen($hex) === 4) { // 'rgba' -> 'rrggbbaa'
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2].$hex[3].$hex[3];
        }

        if (strlen($hex) !== 6 && strlen($hex) !== 8) {
            throw new \InvalidArgumentException("Color must be in '#RRGGBB' or '#RRGGBBAA' format. Given: {$hex}");
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        if (strlen($hex) === 8) {
            // alpha in hex: 00 (opaque) .. FF (transparent)
            $aHex = substr($hex, 6, 2);
            $alphaFraction = hexdec($aHex) / 255; // 0..1 (0 opaque)
            // GD uses 0 (opaque) .. 127 (transparent)
            $gdAlpha = (int) round(127 * $alphaFraction);
            return imagecolorallocatealpha($resource, $r, $g, $b, $gdAlpha);
        }

        // No alpha
        return imagecolorallocate($resource, $r, $g, $b);
    }

    public function drawLine(Image $image, int $x1, int $y1, int $x2, int $y2, string $color, int $thickness = 1): void
    {
        $resource = $image->getDriverResource();

        if ($resource === null) {
            throw new \RuntimeException('GD resource is not available on the Image.');
        }

        // Ensure thickness is at least 1
        $thickness = max(1, (int) $thickness);

        // Save previous thickness if available (imagesetthickness returns void, so track via ini not possible)
        imagesetthickness($resource, $thickness);

        $colIndex = $this->allocateColor($resource, $color);

        imageline($resource, $x1, $y1, $x2, $y2, $colIndex);

        // Reset thickness back to 1 to avoid surprising callers
        imagesetthickness($resource, 1);
        $image->setDriverResource($resource);
    }

    /**
     * Saves the image resource to the specified path in the given format.
     */
    public function saveImage(mixed $resource, string $path, string|FormatInterface $format): bool
    {
        if ($format instanceof FormatInterface) {
            $format = $format->getName();
        }

        $format = strtolower($format);

        switch ($format) {
            case 'jpeg':
            case 'jpg':
                return imagejpeg($resource, $path);
            case 'png':
                return imagepng($resource, $path);
            case 'gif':
                return imagegif($resource, $path);
            default:
                throw new \RuntimeException("Unsupported format '{$format}' for GD driver.");
        }
    }

}
