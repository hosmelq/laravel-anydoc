<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel;

use HosmelQ\Anydoc\Laravel\Contracts\Anydoc;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;

final readonly class FilesystemPendingDisk extends PendingDisk
{
    public function __construct(
        private Anydoc $anydoc,
        private Filesystem $filesystem,
    ) {
    }

    public function file(string $path): PendingConversion
    {
        $bytes = $this->filesystem->get($path);

        if ($bytes === null) {
            throw new FileNotFoundException("File does not exist at path {$path}.");
        }

        $format = $this->anydoc->formatFromBytes($bytes)
            ?? $this->anydoc->formatFromPath($path);

        return $this->anydoc->bytes($bytes, $format);
    }
}
