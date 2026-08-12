<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel;

use Anydoc\Document;
use HosmelQ\Anydoc\Laravel\Enums\Format;

final readonly class PendingBytesConversion extends PendingConversion
{
    public function __construct(private string $bytes, private null|Format $format)
    {
    }

    public function document(): Document
    {
        return anydoc_to_document($this->bytes, $this->format?->value);
    }

    public function markdown(): string
    {
        return anydoc_to_markdown_bytes($this->bytes, $this->format?->value);
    }
}
