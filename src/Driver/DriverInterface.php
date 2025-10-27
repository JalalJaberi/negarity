<?php

namespace Negarity\Driver;

use Negarity\IO\Source\SourceInterface;
use Negarity\Core\Image;
use Negarity\IO\Format\FormatInterface;

interface DriverInterface
{
    /**
     * Checks if this driver is available in the current environment.
     */
    public function isAvailable(): bool;

    /**
     * Creates a low-level image resource from raw pixel data.
     *
     * @param mixed $rawPixels Raw pixel data specific to the driver.
     */
    public function createResource(mixed $rawPixels): mixed;

    /**
     * Creates an Image object from a SourceInterface.
     */
    public function createImageFromSource(SourceInterface $source): Image;

    /**
     * Returns the driver name (e.g., 'gd', 'imagick', 'vips').
     */
    public function getName(): string;

    /**
     * Returns the width of the given image resource.
     *
     * @param mixed $resource The underlying image resource or object.
     */
    public function getWidth(mixed $resource): int;

    /**
     * Returns the height of the given image resource.
     *
     * @param mixed $resource The underlying image resource or object.
     */
    public function getHeight(mixed $resource): int;

    /**
     * Extracts raw pixel data from the given Image object.
     *
     * @param Image $image The image to extract data from.
     * @return mixed The raw pixel data specific to the driver.
     */
    public function extractData(Image $image): mixed;

    /* *
     * Draw a line on the given image resource.
     *
     * @param Image $image The image to draw on.
     * @param int $x1     Starting x-coordinate.
     * @param int $y1     Starting y-coordinate.
     * @param int $x2     Ending x-coordinate.
     * @param int $y2     Ending y-coordinate.
     * @param string $color   Color of the line (e.g., "#ff0000").
     * @param int $thickness Thickness of the line in pixels.
     */
    public function drawLine(Image $image, int $x1, int $y1, int $x2, int $y2, string $color, int $thickness = 1): void;

    /**
     * Save the given image resource to a path in a specific format.
     *
     * @param mixed $resource The underlying image resource or object.
     * @param string $path     Destination file path.
     * @param string|FormatInterface $format   Format name (e.g. "jpeg", "png").
     */
    public function saveImage(mixed $resource, string $path, string|FormatInterface $format): bool;
}
