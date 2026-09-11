<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel;

use Anydoc\Document;
use Anydoc\Exception\NeedsOcrException;
use HosmelQ\Anydoc\Laravel\Enums\Format;
use Illuminate\Support\Facades\App;

final readonly class PendingBytesConversion extends PendingConversion
{
    public function __construct(private string $bytes, private null|Format $format)
    {
    }

    public function document(): Document
    {
        return anydoc_to_document($this->bytes, $this->format?->value);
    }

    public function markdown(bool $ocr = false): string
    {
        try {
            return anydoc_to_markdown_bytes($this->bytes, $this->format?->value);
        } catch (NeedsOcrException $needsOcrException) {
            if (! $ocr) {
                throw $needsOcrException;
            }

            return App::make(HostedOcr::class)->convert($this->bytes);
        }
    }
}
