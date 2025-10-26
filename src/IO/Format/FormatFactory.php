<?php

namespace Negarity\IO\Format;

use Negarity\IO\Format\{
    FormatInterface,
    PngFormat,
    JpegFormat,
    WebpFormat
};
use RuntimeException;

class FormatFactory
{
    public static function createFormat(string $format): FormatInterface
    {
        return match (strtolower($format)) {
            'png' => new PngFormat(),
            'jpeg', 'jpg' => new JpegFormat(),
            'webp' => new WebpFormat(),
            default => throw new RuntimeException("Unsupported image format: $format"),
        };
    }
}
