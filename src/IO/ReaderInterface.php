<?php

namespace Negarity\IO;

use Negarity\IO\Source\SourceInterface;
use Negarity\Core\Image;

interface ReaderInterface
{
    public function load(SourceInterface $source): mixed;
}
