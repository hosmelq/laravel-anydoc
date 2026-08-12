<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel;

use Anydoc\Document;
use Closure;
use HosmelQ\Anydoc\Laravel\Contracts\Anydoc;
use HosmelQ\Anydoc\Laravel\Enums\ConversionOutput;
use HosmelQ\Anydoc\Laravel\Enums\ConversionSource;
use HosmelQ\Anydoc\Laravel\Enums\Format;
use HosmelQ\Anydoc\Laravel\Fakes\PendingConversion as FakePendingConversion;
use HosmelQ\Anydoc\Laravel\Fakes\PendingDisk as FakePendingDisk;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Assert as PHPUnit;

final class AnydocFake implements Anydoc
{
    /**
     * @var list<RecordedConversion>
     */
    private array $conversions = [];

    /**
     * @var null|(Closure(RecordedConversion): Document)|Document
     */
    private readonly null|Closure|Document $documentResponse;

    /**
     * @var (Closure(RecordedConversion): string)|string
     */
    private readonly Closure|string $markdownResponse;

    /**
     * @param array<array-key, mixed> $responses
     */
    public function __construct(array $responses = [])
    {
        $documentResponse = null;
        $markdownResponse = '';

        foreach ($responses as $output => $response) {
            $conversionOutput = is_string($output) ? ConversionOutput::tryFrom($output) : null;

            if ($conversionOutput === null) {
                throw new InvalidArgumentException("Unsupported fake response [{$output}].");
            }

            if ($conversionOutput === ConversionOutput::Document) {
                if ($response instanceof Closure) {
                    $documentResponse = static function (RecordedConversion $conversion) use ($response): Document {
                        $document = $response($conversion);

                        if (! $document instanceof Document) {
                            throw new InvalidArgumentException('The document fake response callback must return an Anydoc\\Document.');
                        }

                        return $document;
                    };

                    continue;
                }

                if (! $response instanceof Document) {
                    throw new InvalidArgumentException('The document fake response must be an Anydoc\\Document or Closure.');
                }

                $documentResponse = $response;

                continue;
            }

            if ($response instanceof Closure) {
                $markdownResponse = static function (RecordedConversion $conversion) use ($response): string {
                    $markdown = $response($conversion);

                    if (! is_string($markdown)) {
                        throw new InvalidArgumentException('The markdown fake response callback must return a string.');
                    }

                    return $markdown;
                };

                continue;
            }

            if (! is_string($response)) {
                throw new InvalidArgumentException('The markdown fake response must be a string or Closure.');
            }

            $markdownResponse = $response;
        }

        $this->documentResponse = $documentResponse;
        $this->markdownResponse = $markdownResponse;
    }

    public function assertConverted(null|Closure $callback = null): void
    {
        PHPUnit::assertTrue(
            $this->conversions($callback)->isNotEmpty(),
            'The expected document conversion was not performed.',
        );
    }

    public function assertConvertedTimes(int $times = 1, null|Closure $callback = null): void
    {
        PHPUnit::assertCount(
            $times,
            $this->conversions($callback),
            sprintf('The expected document conversion was not performed %d times.', $times),
        );
    }

    public function assertConvertedToDocument(null|Closure $callback = null): void
    {
        PHPUnit::assertTrue(
            $this->hasConversion(ConversionOutput::Document, $callback),
            'The expected document-model conversion was not performed.',
        );
    }

    public function assertConvertedToMarkdown(null|Closure $callback = null): void
    {
        PHPUnit::assertTrue(
            $this->hasConversion(ConversionOutput::Markdown, $callback),
            'The expected Markdown conversion was not performed.',
        );
    }

    public function assertNotConverted(Closure $callback): void
    {
        PHPUnit::assertTrue(
            $this->conversions($callback)->isEmpty(),
            'The unexpected document conversion was performed.',
        );
    }

    public function assertNothingConverted(): void
    {
        PHPUnit::assertEmpty(
            $this->conversions,
            'Document conversions were performed unexpectedly.',
        );
    }

    public function bytes(string $bytes, null|Format $format = null): FakePendingConversion
    {
        return $this->pending(ConversionSource::Bytes, $bytes, format: $format);
    }

    /**
     * @return Collection<int, RecordedConversion>
     */
    public function conversions(null|Closure $callback = null): Collection
    {
        $conversions = Collection::make($this->conversions);

        if (! $callback instanceof Closure) {
            return $conversions;
        }

        return $conversions->filter(
            fn (RecordedConversion $conversion): bool => $callback($conversion) === true,
        );
    }

    public function convertToDocument(
        ConversionSource $source,
        string|UploadedFile $input,
        null|string $disk,
        null|Format $format,
    ): Document {
        if ($this->documentResponse === null) {
            throw new LogicException('No document response was configured. Pass a document response to Anydoc::fake() before document().');
        }

        $conversion = $this->record($source, $input, ConversionOutput::Document, $disk, $format);
        $response = $this->documentResponse;

        return $response instanceof Closure ? $response($conversion) : $response;
    }

    public function convertToMarkdown(
        ConversionSource $source,
        string|UploadedFile $input,
        null|string $disk,
        null|Format $format,
    ): string {
        $conversion = $this->record($source, $input, ConversionOutput::Markdown, $disk, $format);
        $response = $this->markdownResponse;

        return $response instanceof Closure ? $response($conversion) : $response;
    }

    public function disk(null|string $name = null): FakePendingDisk
    {
        return new FakePendingDisk($this, $name);
    }

    public function file(string|UploadedFile $file): FakePendingConversion
    {
        $source = is_string($file) ? ConversionSource::File : ConversionSource::Upload;

        return $this->pending($source, $file);
    }

    public function formatFromBytes(string $bytes): null|Format
    {
        $format = anydoc_format_from_bytes($bytes);

        return $format === null ? null : Format::from($format);
    }

    public function formatFromExtension(string $extension): null|Format
    {
        $format = anydoc_format_from_extension($extension);

        return $format === null ? null : Format::from($format);
    }

    public function formatFromPath(string $path): null|Format
    {
        $format = anydoc_format_from_path($path);

        return $format === null ? null : Format::from($format);
    }

    public function pending(
        ConversionSource $source,
        string|UploadedFile $input,
        null|string $disk = null,
        null|Format $format = null,
    ): FakePendingConversion {
        return new FakePendingConversion($this, $input, $source, $disk, $format);
    }

    private function hasConversion(
        ConversionOutput $output,
        null|Closure $callback,
    ): bool {
        return $this->conversions($callback)->contains(
            fn (RecordedConversion $conversion): bool => $conversion->output === $output,
        );
    }

    private function record(
        ConversionSource $source,
        string|UploadedFile $input,
        ConversionOutput $output,
        null|string $disk,
        null|Format $format,
    ): RecordedConversion {
        $conversion = new RecordedConversion(
            disk: $disk,
            format: $format,
            input: $input,
            output: $output,
            source: $source,
        );

        $this->conversions[] = $conversion;

        return $conversion;
    }
}
