<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel;

use function Safe\file_get_contents;

use Anydoc\Document;
use Anydoc\Exception\NeedsOcrException;
use ErrorException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\App;

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

    public function markdown(bool $ocr = false): string
    {
        try {
            return anydoc_to_markdown($this->path);
        } catch (NeedsOcrException $needsOcrException) {
            if (! $ocr) {
                throw $needsOcrException;
            }

            return App::make(HostedOcr::class)->convert(file_get_contents($this->path), basename($this->path));
        }
    }
}
