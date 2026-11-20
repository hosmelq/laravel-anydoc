<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel;

use HosmelQ\Anydoc\Laravel\Contracts\Anydoc;
use HosmelQ\Anydoc\Laravel\Enums\Format;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Override;

final readonly class AnydocManager implements Anydoc
{
    public function __construct(
        private Factory $filesystems,
    ) {}

    #[Override]
    public function bytes(string $bytes, null|Format $format = null): PendingBytesConversion
    {
        return new PendingBytesConversion($bytes, $format);
    }

    #[Override]
    public function disk(null|string $name = null): FilesystemPendingDisk
    {
        return new FilesystemPendingDisk($this, $this->filesystems->disk($name));
    }

    #[Override]
    public function file(string|UploadedFile $file): PendingConversion
    {
        if (is_string($file)) {
            return new PendingFileConversion($file);
        }

        $bytes = $file->get();

        if ($bytes === false) {
            throw new FileNotFoundException("File does not exist at path {$file->getPathname()}.");
        }

        $format = $this->formatFromBytes($bytes)
            ?? $this->formatFromPath($file->getClientOriginalName());

        return new PendingBytesConversion($bytes, $format, $file->getClientOriginalName());
    }

    #[Override]
    public function formatFromBytes(string $bytes): null|Format
    {
        $format = anydoc_format_from_bytes($bytes);

        return $format === null ? null : Format::from($format);
    }

    #[Override]
    public function formatFromExtension(string $extension): null|Format
    {
        $format = anydoc_format_from_extension($extension);

        return $format === null ? null : Format::from($format);
    }

    #[Override]
    public function formatFromPath(string $path): null|Format
    {
        $format = anydoc_format_from_path($path);

        return $format === null ? null : Format::from($format);
    }
}
