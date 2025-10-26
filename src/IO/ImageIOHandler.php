<?php

namespace Negarity\IO;

use Negarity\Core\Image;
use Negarity\IO\Source\SourceInterface;
use Negarity\IO\Format\FormatInterface;
use Negarity\Driver\DriverInterface;

/**
 * ImageIOHandler orchestrates the loading and saving of images:
 * - Readers/Writers handle format-specific I/O.
 * - Drivers handle pixel-level operations and resource creation.
 * - Image objects store driver resources and delegate operations to drivers.
 */
final class ImageIOHandler
{
    /** @var ReaderInterface[] */
    private array $readers = [];

    /** @var WriterInterface[] */
    private array $writers = [];

    /** @var DriverInterface */
    private DriverInterface $driver;

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Register a reader for a specific format.
     */
    public function registerReader(string $formatName, ReaderInterface $reader): void
    {
        $this->readers[strtolower($formatName)] = $reader;
    }

    /**
     * Register a writer for a specific format.
     */
    public function registerWriter(string $formatName, WriterInterface $writer): void
    {
        $this->writers[strtolower($formatName)] = $writer;
    }

    /**
     * Register both reader and writer for a format at once.
     */
    public function registerFormat(
        string $formatName,
        ReaderInterface $reader,
        WriterInterface $writer
    ): void {
        $this->registerReader($formatName, $reader);
        $this->registerWriter($formatName, $writer);
    }

    /**
     * Load an image from a source using a specific format.
     */
    public function load(SourceInterface $source, string|FormatInterface $format, ?DriverInterface $driver = null): Image
    {
        $formatName = strtolower($format instanceof FormatInterface ? $format->getName() : $format);

        if (!isset($this->readers[$formatName])) {
            throw new \RuntimeException("No reader registered for format: {$formatName}");
        }

        $reader = $this->readers[$formatName];

        // Reader produces raw pixel data (driver-agnostic)
        $rawData = $reader->load($source);

        $finalDriver = $driver ?? $this->driver;

        // Driver converts raw data into a driver-specific resource
        $driverResource = $finalDriver->createResource($rawData);

        // Build Image object
        return new Image(
            $driverResource,
            $finalDriver->getWidth($driverResource),
            $finalDriver->getHeight($driverResource),
            $format,
            $finalDriver
        );
    }

    /**
     * Save an Image to a destination path using a specific format.
     */
    public function save(Image $image, string|FormatInterface $format, string $destination): void
    {
        $formatName = strtolower($format instanceof FormatInterface ? $format->getName() : $format);

        if (!isset($this->writers[$formatName])) {
            throw new \RuntimeException("No writer registered for format: {$formatName}");
        }

        $writer = $this->writers[$formatName];

        $driver = $image->getDriver();
        if ($driver === null) {
            $driver = $this->driver;
        }
        if ($driver === null) {
            throw new \RuntimeException("No driver available to save the image.");
        }

        // Writer encodes data to format and returns binary string
        $binary = $writer->save($image, $destination);
    }

    /**
     * Set the driver instance.
     */
    public function setDriver(DriverInterface $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * Get the driver instance.
     */
    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }
}
