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
