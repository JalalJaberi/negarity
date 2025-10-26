<?php

namespace Negarity\IO\Format;

class PngFormat implements FormatInterface
{
    public function getName(): string { return 'png'; }
    public function getMimeType(): string { return 'image/png'; }
    public function getExtension(): string { return 'png'; }
}
