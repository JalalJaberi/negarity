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
use Imagick;
use RuntimeException;

class ImagickDriver extends AbstractDriver
{
    public function __construct()
    {
        parent::__construct('imagick');
    }

    public function isAvailable(): bool
    {
        return extension_loaded('imagick');
    }

    public function getWidth(mixed $resource): int
    {
        return $resource->getImageWidth();
    }

    public function getHeight(mixed $resource): int
    {
        return $resource->getImageHeight();
    }

    public function createResource(mixed $rawPixels): mixed
    {
        $imagick = new Imagick();
        $imagick->readImageBlob($rawPixels);
        return $imagick;
    }

    public function createImageFromSource(SourceInterface $source): Image
    {
        $imagick = new Imagick();

        if ($source instanceof MemorySource) {
            $width = $source->getWidth();
            $height = $source->getHeight();
            $color = $source->getColor() ?? 'transparent';
            $imagick->newImage($width, $height, new \ImagickPixel($color));
            $imagick->setImageFormat('png');
            return new Image($imagick, $width, $height, 'png', $this);
        } elseif ($source instanceof FileSource) {
            $imagick->readImage($source->getIdentifier());
        } elseif ($source instanceof Base64Source) {
            $imagick->readImageBlob(base64_decode($source->getData()));
        } else {
            throw new RuntimeException('Unsupported source for Imagick driver.');
        }

        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();
        $format = strtolower($imagick->getImageFormat());

        return new Image($imagick, $width, $height, $format, $this);
    }

    public function extractData(Image $image): mixed
    {
        $format = $image->getFormat();
        if ($format instanceof FormatInterface) {
            $format = $format->getName();
        }

        switch (strtolower($format)) {
            case 'jpeg':
            case 'jpg':
                $image->getDriverResource()->setImageFormat('JPEG');
                break;
            case 'png':
                $image->getDriverResource()->setImageFormat('PNG');
                break;
            case 'gif':
                $image->getDriverResource()->setImageFormat('GIF');
                break;
            default:
                $image->getDriverResource()->setImageFormat(strtoupper($format));
                break;
        }
        return $image->getDriverResource()->getImageBlob();
    }

    public function drawLine(Image $image, int $x1, int $y1, int $x2, int $y2, string $color, int $thickness = 1): void
    {
        $draw = new \ImagickDraw();
        $draw->setStrokeColor(new \ImagickPixel($color));
        $draw->setStrokeWidth($thickness);
        $draw->line($x1, $y1, $x2, $y2);
        $image->getDriverResource()->drawImage($draw);
    }

    /**
     * Saves the given image resource to the specified path in the given format.
     */
    public function saveImage(mixed $resource, string $path, string|FormatInterface $format): bool
    {
        if ($format instanceof FormatInterface) {
            $format = $format->getName();
        }

        $resource->setImageFormat(strtoupper($format));
        $result = $resource->writeImage($path);

        if (!$result) {
            throw new \RuntimeException("Failed to save image with Imagick driver.");
        }

        return true;
    }
}