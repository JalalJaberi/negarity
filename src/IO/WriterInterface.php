<?php

namespace Negarity\IO;

use Negarity\IO\Source\SourceInterface;
use Negarity\Core\Image;

interface WriterInterface
{
    public function save(Image $image, string $destination): bool;
}
