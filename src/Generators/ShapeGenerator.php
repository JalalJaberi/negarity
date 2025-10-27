<?php

namespace Negarity\Generators;

use Negarity\Core\Image;
use Negarity\Core\Size;
use Negarity\Driver\DriverInterface;
use Negarity\Core\Color;

class ShapeGenerator implements GeneratorInterface
{
    public function getName(): string
    {
        return 'Shape Generator';
    }

    public function getOptionsSchema(): array
    {
        return [
            'shape' => ['type' => 'string', 'default' => 'line', 'options' => ['line', 'rectangle', 'ellipse']],
            'line-width' => ['type' => 'int', 'default' => 1],
            'line-color' => ['type' => 'string', 'default' => '#000000'],
            'filled' => ['type' => 'bool', 'default' => true],
            'fill-color' => ['type' => 'string', 'default' => '#ffffff'],
            'points' => ['type' => 'array', 'default' => []], // For future use
        ];
    }

    public function generate(Image $image, array $options = []): void
    {
        $shape = $options['shape'] ?? 'rectangle';
        $lineWidth = $options['line-width'] ?? 1;
        $lineColor = $options['line-color'] ?? '#000000';
        $filled = $options['filled'] ?? true;
        $fillColor = $options['fill-color'] ?? '#ffffff';
        $points = $options['points'] ?? [];

        $driver = $image->getDriver();

        // Draw the shape — we’ll assume drivers have a generic draw API
        switch ($shape) {
            case 'ellipse':
                $driver->drawEllipse($image->getDriverResource(), $size, $lineColor, $filled);
                break;
            case 'line':
                // check the size of points to be 4
                if (count($points) === 4) {
                    $driver->drawLine($image, $points[0], $points[1], $points[2], $points[3], $lineColor, $lineWidth);
                } else {
                    throw new \InvalidArgumentException("Points array must contain exactly 4 elements for line shape.");
                }
                break;
            case 'rectangle':
            default:
                $driver->drawRectangle($image, $image->getWidth(), $image->getHeight(), $lineColor, $filled);
                break;
        }
    }
}
