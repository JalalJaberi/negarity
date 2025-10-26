<?php

namespace Negarity;

use Negarity\Core\Image;
use Negarity\Driver\DriverManager;
use Negarity\IO\{
    ImageIOHandler,
    ReaderInterface,
    WriterInterface,
    JpegReader,
    JpegWriter,
    PngReader,
    PngWriter,
    WebpReader,
    WebpWriter
};
use Negarity\IO\Source\{
    SourceInterface,
    MemorySource,
    FileSource
};
use RuntimeException;

/**
 * Negarity — Image Processing Library for PHP
 *
 * @package Negarity
 * @version 0.1.0
 */
final class Negarity
{
    public const VERSION = '0.1.0';

    /** @var DriverManager|null */
    private static ?DriverManager $driverManager = null;

    /** @var ImageIOHandler|null */
    private static ?ImageIOHandler $ioHandler = null;

    /**
     * Initializes Negarity’s core systems.
     */
    public static function init(?DriverManager $customManager = null): void
    {
        if (self::$driverManager === null) {
            self::$driverManager = $customManager ?? new DriverManager();
            self::$driverManager->detectAvailableDrivers();
        }

        if (self::$ioHandler === null) {
            self::$ioHandler = new ImageIOHandler(self::$driverManager->getCurrentDriver());
            // Register default formats
            self::$ioHandler->registerFormat('jpeg', new JpegReader(), new JpegWriter());
            self::$ioHandler->registerFormat('png', new PngReader(), new PngWriter());
            self::$ioHandler->registerFormat('webp', new WebpReader(), new WebpWriter());
        }
    }

    /**
     * Creates a new Image object.
     *
     * Accepts a string path, or a SourceInterface.
     */
    public static function image(string|SourceInterface $input, string|FormatInterface $format, ?string $driver = null): Image
    {
        self::ensureInitialized();

        $driverInstance = $driver
        ? self::$driverManager->getDriver($driver)
        : self::$driverManager->getCurrentDriver();

        if (!$driverInstance) {
            throw new RuntimeException("No suitable driver found for '{$driver}'.");
        }

        self::$ioHandler->setDriver($driverInstance);

        if ($input instanceof SourceInterface) {
            // Load using IO Handler
            return self::$ioHandler->load($input, $format);
        } elseif (is_string($input)) {
            $source = new FileSource($input);
            return self::$ioHandler->load($source, $format);
        }
    }

    /**
     * Creates a blank image with specified dimensions and background color.
     */
    public static function createBlank(
    int $width,
    int $height,
    string|FormatInterface $format,
    string $color = '#ffffff',
    ?string $driver = null
    ): Image {
        self::ensureInitialized();

        $driverInstance = $driver
            ? self::$driverManager->getDriver($driver)
            : self::$driverManager->getDefaultDriver();

        $source = new MemorySource($width, $height, $color);

        return self::$ioHandler->load($source, $format, $driverInstance);
    }

    public static function saveImage(Image $image, string|FormatInterface $format, string $path): void
    {
        self::ensureInitialized();
        self::$ioHandler->save($image, $format, $path);
    }

    /**
     * Returns the current driver manager.
     */
    public static function driver(): DriverManager
    {
        self::ensureInitialized();
        return self::$driverManager;
    }

    /**
     * Checks if Negarity is initialized.
     */
    public static function isInitialized(): bool
    {
        return self::$driverManager !== null;
    }

    /**
     * Ensures Negarity is ready.
     */
    private static function ensureInitialized(): void
    {
        if (!self::isInitialized()) {
            self::init();
        }
    }
}
