<?php
require __DIR__ . '/../vendor/autoload.php';

use Negarity\Negarity;
use Negarity\Core\Image;

// Initialize Negarity (optional, usually automatic)
Negarity::init();

// Create a blank image — choose width, height, and background color
// Optional second argument: driver name ('gd', 'imagick', 'vips')
$image = Negarity::createBlank(400, 300, 'webp', '#ffcc00', driver: 'vips');

// Save to file
Negarity::saveImage($image, 'webp', __DIR__ . '/blank_output.webp');

echo "Blank image created and saved successfully!" . PHP_EOL;
