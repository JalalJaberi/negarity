<?php

namespace Negarity\Driver;

use Negarity\IO\Format\FormatInterface;
use Negarity\IO\Source\SourceInterface;
use Negarity\Core\Image;

abstract class AbstractDriver implements DriverInterface
{
    protected string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * getWidth must be implemented by concrete drivers.
     */
    abstract public function getWidth(mixed $resource): int;

    /**
     * getHeight must be implemented by concrete drivers.
     */
    abstract public function getHeight(mixed $resource): int;

    /**
     * isAvailable must be implemented by concrete drivers.
     */
    abstract public function isAvailable(): bool;

    /**
     * createResource must be implemented by concrete drivers.
     */
    abstract public function createResource(mixed $rawPixels): mixed;

    /**
     * createImageFromSource must be implemented by concrete drivers.
     */
    abstract public function createImageFromSource(\Negarity\IO\Source\SourceInterface $source): \Negarity\Core\Image;

    /**
     * extractData must be implemented by concrete drivers.
     */
    abstract public function extractData(Image $image): mixed;

    /**
     * Draw a line on the given image resource.
     */
    abstract function drawLine(Image $image, int $x1, int $y1, int $x2, int $y2, string $color, int $thickness = 1): void;

    /**
     * saveImage must be implemented by concrete drivers.
     */
    abstract public function saveImage(mixed $imageResource, string $path, string|FormatInterface $format): bool;
}
