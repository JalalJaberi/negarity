<?php

namespace Negarity\Driver;

use Negarity\Driver\GdDriver;
use Negarity\Driver\ImagickDriver;
use Negarity\Driver\VipsDriver;
use RuntimeException;

/**
 * Manages available image drivers and provides the active driver.
 */
class DriverManager
{
    /** @var DriverInterface[] */
    private array $drivers = [];

    /** @var DriverInterface|null */
    private ?DriverInterface $currentDriver = null;

    /**
     * Registers built-in drivers and detects which are available.
     */
    public function detectAvailableDrivers(): void
    {
        // Clear existing drivers
        $this->drivers = [];

        // Add drivers if extensions are available
        if (extension_loaded('gd')) {
            $gd = new GdDriver();
            if ($gd->isAvailable()) {
                $this->drivers['gd'] = $gd;
            }
        }

        if (extension_loaded('imagick')) {
            $imagick = new ImagickDriver();
            if ($imagick->isAvailable()) {
                $this->drivers['imagick'] = $imagick;
            }
        }

        if (extension_loaded('vips')) {
            $vips = new VipsDriver();
            if ($vips->isAvailable()) {
                $this->drivers['vips'] = $vips;
            }
        }

        // Set the first available driver as current
        $this->currentDriver = reset($this->drivers) ?: null;
    }

    /**
     * Returns the currently active driver.
     *
     * @throws RuntimeException if no driver is available
     */
    public function getCurrentDriver(): DriverInterface
    {
        if (!$this->currentDriver) {
            throw new RuntimeException('No available image driver detected.');
        }

        return $this->currentDriver;
    }

    /**
     * Returns the specified driver.
     *
     * @throws RuntimeException if no driver is available
     */
    public function getDriver(string $name): ?DriverInterface
    {
        return $this->drivers[$name] ?? null;
    }

    /**
     * Manually sets the current driver by name.
     *
     * @throws RuntimeException if the driver does not exist
     */
    public function setCurrentDriver(string $name): void
    {
        if (!isset($this->drivers[$name])) {
            throw new RuntimeException("Driver '$name' is not registered or available.");
        }

        $this->currentDriver = $this->drivers[$name];
    }

    /**
     * Returns all registered drivers.
     *
     * @return DriverInterface[]
     */
    public function getAvailableDrivers(): array
    {
        return $this->drivers;
    }

    /**
     * Registers a custom driver at runtime.
     */
    public function registerDriver(string $name, DriverInterface $driver): void
    {
        if ($driver->isAvailable()) {
            $this->drivers[$name] = $driver;

            // If no current driver is set, make this the default
            if ($this->currentDriver === null) {
                $this->currentDriver = $driver;
            }
        }
    }
}
