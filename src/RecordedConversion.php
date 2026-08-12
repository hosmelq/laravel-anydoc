<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel;

use HosmelQ\Anydoc\Laravel\Enums\ConversionOutput;
use HosmelQ\Anydoc\Laravel\Enums\ConversionSource;
use HosmelQ\Anydoc\Laravel\Enums\Format;
use Illuminate\Http\UploadedFile;

final readonly class RecordedConversion
{
    public function __construct(
        public null|string $disk,
        public null|Format $format,
        public string|UploadedFile $input,
        public ConversionOutput $output,
        public ConversionSource $source,
    ) {
    }
}
