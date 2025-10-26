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

    /**
     * Save the given image resource to a path in a specific format.
     *
     * @param mixed $resource The underlying image resource or object.
     * @param string $path     Destination file path.
     * @param string|FormatInterface $format   Format name (e.g. "jpeg", "png").
     */
    public function saveImage(mixed $resource, string $path, string|FormatInterface $format): bool;
}
