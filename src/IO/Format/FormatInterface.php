<?php

namespace Negarity\IO\Format;

interface FormatInterface
{
    public function getName(): string;     // e.g. "jpeg"
    public function getMimeType(): string; // e.g. "image/jpeg"
    public function getExtension(): string;// e.g. ".jpg"
}
