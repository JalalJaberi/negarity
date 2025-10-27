<?php

namespace Negarity\Generators;

use Negarity\Core\Image;
use Negarity\Core\Size;
use Negarity\Driver\DriverInterface;

interface GeneratorInterface
{
    /**
     * Generate an image using the configured driver.
     *
     * @param Image $image The image to modify.
     * @param array $options Configuration options for the generator.
     */
    public function generate(Image $image, array $options = []): void;

    /**
     * Get a human-readable name for this generator (e.g., "Shape Generator").
     */
    public function getName(): string;

    /**
     * Describe available configurable parameters.
     * Could be used for UI editors or scripting integrations.
     */
    public function getOptionsSchema(): array;
}
