<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel\Fakes;

use HosmelQ\Anydoc\Laravel\AnydocFake;
use HosmelQ\Anydoc\Laravel\Enums\ConversionSource;
use HosmelQ\Anydoc\Laravel\PendingDisk as BasePendingDisk;

final readonly class PendingDisk extends BasePendingDisk
{
    public function __construct(
        private AnydocFake $fake,
        private null|string $name,
    ) {
    }

    public function file(string $path): PendingConversion
    {
        return $this->fake->pending(
            ConversionSource::Disk,
            $path,
            $this->name,
        );
    }
}
