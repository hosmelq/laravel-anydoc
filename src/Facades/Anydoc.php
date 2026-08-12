<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel\Facades;

use Anydoc\Document;
use Closure;
use HosmelQ\Anydoc\Laravel\AnydocFake;
use HosmelQ\Anydoc\Laravel\Contracts\Anydoc as AnydocContract;
use HosmelQ\Anydoc\Laravel\Enums\Format;
use HosmelQ\Anydoc\Laravel\PendingConversion;
use HosmelQ\Anydoc\Laravel\PendingDisk;
use HosmelQ\Anydoc\Laravel\RecordedConversion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void assertConverted(null|Closure $callback = null)
 * @method static void assertConvertedTimes(int $times = 1, null|Closure $callback = null)
 * @method static void assertConvertedToDocument(null|Closure $callback = null)
 * @method static void assertConvertedToMarkdown(null|Closure $callback = null)
 * @method static void assertNotConverted(Closure $callback)
 * @method static void assertNothingConverted()
 * @method static PendingConversion bytes(string $bytes, null|Format $format = null)
 * @method static Collection<int, RecordedConversion> conversions(null|Closure $callback = null)
 * @method static PendingDisk disk(null|string $name = null)
 * @method static PendingConversion file(string|UploadedFile $file)
 * @method static null|Format formatFromBytes(string $bytes)
 * @method static null|Format formatFromExtension(string $extension)
 * @method static null|Format formatFromPath(string $path)
 */
final class Anydoc extends Facade
{
    /**
     * @param array{
     *     document?: (Closure(RecordedConversion): Document)|Document,
     *     markdown?: (Closure(RecordedConversion): string)|string
     * } $responses
     */
    public static function fake(array $responses = []): AnydocFake
    {
        return tap(new AnydocFake($responses), function (AnydocFake $fake): void {
            self::swap($fake);
        });
    }

    protected static function getFacadeAccessor(): string
    {
        return AnydocContract::class;
    }
}
