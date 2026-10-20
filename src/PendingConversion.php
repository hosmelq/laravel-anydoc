<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel;

use Anydoc\Document;
use Anydoc\Exception\NeedsOcrException;
use Closure;
use Illuminate\Support\Facades\App;

abstract readonly class PendingConversion
{
    abstract public function document(): Document;

    abstract public function markdown(bool $ocr = false): string;

    /**
     * @param Closure(): string $convert
     * @param Closure(): string $bytes
     */
    protected function markdownWithOcrFallback(Closure $convert, bool $ocr, Closure $bytes, string $filename): string
    {
        try {
            return $convert();
        } catch (NeedsOcrException $needsOcrException) {
            if (! $ocr) {
                throw $needsOcrException;
            }

            return App::make(HostedOcr::class)->convert($bytes(), $filename);
        }
    }
}
