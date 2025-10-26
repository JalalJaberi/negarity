<?php

namespace Negarity\Driver;

use Negarity\Core\Image;
use Negarity\IO\Source\{
    SourceInterface,
    FileSource
};
use Negarity\IO\Format\FormatInterface;
use Jcupitt\Vips\Image as VipsImage;
use RuntimeException;

class VipsDriver extends AbstractDriver
{
    public function __construct()
    {
        parent::__construct('vips');
    }

    public function isAvailable(): bool
    {
        return extension_loaded('vips');
    }

    public function getWidth(mixed $resource): int
    {
        return $resource->width;
    }

    public function getHeight(mixed $resource): int
    {
        return $resource->height;
    }

    public function createResource(mixed $rawPixels): mixed
    {
        return VipsImage::newFromBuffer($rawPixels);
    }

    public function createImageFromSource(SourceInterface $source): Image
    {
        if (!$source instanceof FileSource) {
            throw new RuntimeException('Vips driver currently supports only FileSource.');
        }

        $vips = VipsImage::newFromFile($source->getIdentifier(), ['access' => 'sequential']);
        $width = $vips->width;
        $height = $vips->height;
        $format = strtolower(pathinfo($source->getIdentifier(), PATHINFO_EXTENSION));

        return new Image($vips, $width, $height, $format, $this);
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
                $buffer = $image->getDriverResource()->writeToBuffer('.jpg');
                break;
            case 'png':
                $buffer = $image->getDriverResource()->writeToBuffer('.png');
                break;
            case 'gif':
                $buffer = $image->getDriverResource()->writeToBuffer('.gif');
                break;
            default:
                $buffer = $image->getDriverResource()->writeToBuffer('.' . strtolower($format));
                break;
        }

        return $buffer;
    }

    /**
     * Saves the given image resource to the specified path in the given format.
     */
    public function saveImage(mixed $resource, string $path, string|FormatInterface $format): bool
    {
        try {
            if ($format instanceof FormatInterface) {
                $format = $format->getName();
            }

            $resource->writeToFile($path);
            return true;
        } catch (\Throwable $e) {
            throw new \RuntimeException("Vips failed to save image: " . $e->getMessage(), 0, $e);
        }
    }
}
