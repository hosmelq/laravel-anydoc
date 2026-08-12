<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel;

abstract readonly class PendingDisk
{
    abstract public function file(string $path): PendingConversion;
}
