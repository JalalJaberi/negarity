<?php
require __DIR__ . '/../vendor/autoload.php';

use Negarity\Negarity;
use Negarity\Core\Image;
use Negarity\Generators\ShapeGenerator;

// Initialize Negarity (optional, happens automatically if not called)
Negarity::init();

// Read an image from file
$image = Negarity::image(__DIR__ . '/sample.jpg', 'jpeg', 'vips');

// Create a ShapeGenerator instance
$shapeGenerator = new ShapeGenerator();
// Define options to draw a line
$options = [
    'shape' => 'line',
    'line-width' => 5,
    'line-color' => '#ff0000',
    'points' => [0, 0, $image->getWidth(), $image->getHeight()], // x1, y1, x2, y2
];
// Generate the line on the image
$shapeGenerator->generate($image, $options);

// Save the modified image to a new file
Negarity::saveImage($image, 'jpeg', __DIR__ . '/output.jpg');
