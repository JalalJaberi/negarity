<?php
require __DIR__ . '/../vendor/autoload.php';

use Negarity\Negarity;
use Negarity\Core\Image;

// Initialize Negarity (optional, happens automatically if not called)
Negarity::init();

// Read an image from file
$image = Negarity::image(__DIR__ . '/sample.jpg', 'jpeg', 'imagick');

// Inspect basic properties
echo "Width: " . $image->getWidth() . PHP_EOL;
echo "Height: " . $image->getHeight() . PHP_EOL;