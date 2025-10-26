<?php

namespace Negarity\Core;

use Negarity\Driver\DriverInterface;
use Negarity\Negarity;
use Negarity\IO\Source\{
    Base64Source,
    FileSource,
    MemorySource,
    SourceInterface
};
use Negarity\IO\Format\{
    FormatInterface,
    PngFormat,
    FormatFactory
};
use RuntimeException;

class Image
{
    private int $width;
    private int $height;
    private mixed $driverResource;  // driver-specific resource
    private ?FormatInterface $format;   // e.g., 'jpeg', 'png', etc.
    private ?DriverInterface $driver;   // driver used to create this image

    // -------------------------
    // FACTORY METHODS
    // -------------------------

    public static function createBlank(int $width, int $height, ?string $color = null, ?DriverInterface $driver = null): self
    {
        $image = new self();
        $image->width = $width;
        $image->height = $height;
        $image->format = new PngFormat(); // Default format for blank images
        $image->driver = $driver ?? Negarity::driver()->getCurrentDriver();

        // Create blank resource using driver and MemorySource
        $memorySource = new MemorySource($width, $height, $color);
        $imageResource = $image->driver->createImageFromSource($memorySource);

        $image->driverResource = $imageResource->getDriverResource();
        $image->width = $imageResource->getWidth();
        $image->height = $imageResource->getHeight();
        $image->format = $imageResource->getFormat();

        return $image;
    }

    // -------------------------
    // CONSTRUCTOR
    // -------------------------
    public function __construct(
        mixed $driverResource,
        int $width,
        int $height,
        string|FormatInterface|null $format,
        ?DriverInterface $driver = null
    ) {
        $this->driverResource = $driverResource;
        $this->width = $width;
        $this->height = $height;
        if ($format instanceof FormatInterface) {
            $this->format = $format;
        } elseif (is_string($format)) {
            $this->format = FormatFactory::createFormat($format);
        }
        $this->driver = $driver;
    }

    // -------------------------
    // GETTERS
    // -------------------------
    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getFormat(): ?FormatInterface
    {
        return $this->format;
    }

    public function getDriverResource(): mixed
    {
        return $this->driverResource;
    }

    public function getDriver(): ?DriverInterface
    {
        return $this->driver;
    }

    // -------------------------
    // PLACEHOLDER save/export
    // -------------------------
    public function save(string $path, ?FormatInterface $format = null): bool
    {
        $imageResource = $this->driverResource;
        $imageFormat = $format ?? $this->format;

        // Delegate to the driver for saving
        return $this->driver->saveImage($imageResource, $path, $imageFormat);
    }
}
