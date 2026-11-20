<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel;

use Anydoc\Document;
use HosmelQ\Anydoc\Laravel\Enums\Format;
use Override;

final readonly class PendingBytesConversion extends PendingConversion
{
    public function __construct(
        private string $bytes,
        private null|Format $format,
        private string $filename = 'document.pdf',
    ) {}

    #[Override]
    public function document(): Document
    {
        return anydoc_to_document($this->bytes, $this->format?->value);
    }

    #[Override]
    public function markdown(bool $ocr = false): string
    {
        return $this->markdownWithOcrFallback(
            fn (): string => anydoc_to_markdown_bytes($this->bytes, $this->format?->value),
            $ocr,
            fn (): string => $this->bytes,
            $this->filename,
        );
    }
}
