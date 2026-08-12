<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel\Contracts;

use HosmelQ\Anydoc\Laravel\Enums\Format;
use HosmelQ\Anydoc\Laravel\PendingConversion;
use HosmelQ\Anydoc\Laravel\PendingDisk;
use Illuminate\Http\UploadedFile;

interface Anydoc
{
    public function bytes(string $bytes, null|Format $format = null): PendingConversion;

    public function disk(null|string $name = null): PendingDisk;

    public function file(string|UploadedFile $file): PendingConversion;

    public function formatFromBytes(string $bytes): null|Format;

    public function formatFromExtension(string $extension): null|Format;

    public function formatFromPath(string $path): null|Format;
}
