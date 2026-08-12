<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel\Fakes;

use Anydoc\Document;
use HosmelQ\Anydoc\Laravel\AnydocFake;
use HosmelQ\Anydoc\Laravel\Enums\ConversionSource;
use HosmelQ\Anydoc\Laravel\Enums\Format;
use HosmelQ\Anydoc\Laravel\PendingConversion as BasePendingConversion;
use Illuminate\Http\UploadedFile;

final readonly class PendingConversion extends BasePendingConversion
{
    public function __construct(
        private AnydocFake $fake,
        private string|UploadedFile $input,
        private ConversionSource $source,
        private null|string $disk = null,
        private null|Format $format = null,
    ) {
    }

    public function document(): Document
    {
        return $this->fake->convertToDocument(
            $this->source,
            $this->input,
            $this->disk,
            $this->format,
        );
    }

    public function markdown(): string
    {
        return $this->fake->convertToMarkdown(
            $this->source,
            $this->input,
            $this->disk,
            $this->format,
        );
    }
}
