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

    private function parseHexColor(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return [$r, $g, $b, 255];
    }

    /**
     * Draw a line on a Vips-backed Image; supports thickness via SVG fallback.
     */
    public function drawLine(Image $image, int $x1, int $y1, int $x2, int $y2, string $color, int $thickness = 1): void
    {
        $vips = $image->getDriverResource();
        [$r, $g, $b, $a] = $this->parseHexColor($color);

        // Create a 4-band RGBA base (not 1-band!)
        $lineImg = VipsImage::black($vips->width, $vips->height)
            ->bandjoin_const([0, 0, 0])       // expand to 4 bands (RGBA)
            ->copy(['interpretation' => 'srgb']);

        // Draw the line (1px thick)
        $lineImg = $lineImg->draw_line([$r, $g, $b, $a], $x1, $y1, $x2, $y2);

        // For thicker lines, approximate by drawing parallel lines
        if ($thickness > 1) {
            $dx = $x2 - $x1;
            $dy = $y2 - $y1;
            $len = max(1.0, sqrt($dx*$dx + $dy*$dy));
            $ux = -$dy / $len;
            $uy =  $dx / $len;
            $half = (int) floor(($thickness - 1) / 2);
            for ($i = -$half; $i <= $half; $i++) {
                $ox = (int) round($ux * $i);
                $oy = (int) round($uy * $i);
                $lineImg = $lineImg->draw_line([$r, $g, $b, $a], $x1 + $ox, $y1 + $oy, $x2 + $ox, $y2 + $oy);
            }
        }

        // Composite line over the base image
        $composited = $vips->composite2($lineImg, 'over');
        $image->setDriverResource($composited);
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
