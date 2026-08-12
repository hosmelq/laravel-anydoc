<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel;

use function Safe\file_get_contents;

use Anydoc\Document;
use ErrorException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

final readonly class PendingFileConversion extends PendingConversion
{
    public function __construct(private string $path)
    {
    }

    public function document(): Document
    {
        try {
            $bytes = file_get_contents($this->path);
        } catch (ErrorException $errorException) {
            throw new FileNotFoundException(
                $errorException->getMessage(),
                0,
                $errorException,
            );
        }

        $format = anydoc_format_from_bytes($bytes) ?? anydoc_format_from_path($this->path);

        return anydoc_to_document($bytes, $format);
    }

    public function markdown(): string
    {
        return anydoc_to_markdown($this->path);
    }
}
