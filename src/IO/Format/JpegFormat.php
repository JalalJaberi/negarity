<?php

namespace Negarity\IO\Format;

class JpegFormat implements FormatInterface
{
    public function getName(): string { return 'jpeg'; }
    public function getMimeType(): string { return 'image/jpeg'; }
    public function getExtension(): string { return 'jpg'; }
}
