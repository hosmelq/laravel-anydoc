<?php

declare(strict_types=1);

use HosmelQ\Anydoc\Laravel\AnydocFake;
use HosmelQ\Anydoc\Laravel\Enums\ConversionOutput;
use HosmelQ\Anydoc\Laravel\Enums\ConversionSource;
use HosmelQ\Anydoc\Laravel\Enums\Format;
use HosmelQ\Anydoc\Laravel\Facades\Anydoc;
use HosmelQ\Anydoc\Laravel\RecordedConversion;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\AssertionFailedError;

it('fails assertions when expected conversions are missing', function (): void {
    Anydoc::fake();

    expect(fn () => Anydoc::assertConverted())
        ->toThrow(AssertionFailedError::class);
});

it('rejects invalid document callback responses', function (): void {
    Anydoc::fake([
        'document' => fn (): string => 'not a document',
    ]);

    expect(fn () => Anydoc::bytes('bytes', Format::Csv)->document())
        ->toThrow(InvalidArgumentException::class, 'The document fake response callback must return an Anydoc\\Document.');
});

it('rejects invalid document fake responses', function (): void {
    expect(fn (): AnydocFake => Anydoc::fake([
        'document' => 'not a document',
    ]))
        ->toThrow(InvalidArgumentException::class, 'The document fake response must be an Anydoc\\Document or Closure.');
});

it('rejects invalid markdown callback responses', function (): void {
    Anydoc::fake([
        'markdown' => fn (): int => 42,
    ]);

    expect(fn () => Anydoc::bytes('bytes')->markdown())
        ->toThrow(InvalidArgumentException::class, 'The markdown fake response callback must return a string.');
});

it('rejects invalid markdown fake responses', function (): void {
    expect(fn (): AnydocFake => Anydoc::fake([
        'markdown' => 42,
    ]))
        ->toThrow(InvalidArgumentException::class, 'The markdown fake response must be a string or Closure.');
});

it('rejects unsupported fake responses', function (): void {
    expect(fn (): AnydocFake => Anydoc::fake([
        'pdf' => 'not supported',
    ]))
        ->toThrow(InvalidArgumentException::class, 'Unsupported fake response [pdf].');
});

it('requires an explicit document response', function (): void {
    Anydoc::fake();

    expect(fn () => Anydoc::bytes('bytes', Format::Csv)->document())
        ->toThrow(LogicException::class);
});

it('keeps native format detection available', function (): void {
    Anydoc::fake();

    expect(Anydoc::formatFromExtension('.xlsm'))
        ->toBe(Format::Xlsx);
});

it('records bytes, files, uploads, and disk paths', function (): void {
    $fake = Anydoc::fake();

    $upload = UploadedFile::fake()->createWithContent('report.csv', 'name');

    Anydoc::bytes('bytes', Format::Csv)->markdown();
    Anydoc::disk('documents')->file('report.docx')->markdown();
    Anydoc::file('/documents/report.pdf')->markdown();
    Anydoc::file($upload)->markdown();

    $fake->assertConvertedTimes(4);

    expect($fake->conversions()->pluck('source')->all())
        ->toBe([
            ConversionSource::Bytes,
            ConversionSource::Disk,
            ConversionSource::File,
            ConversionSource::Upload,
        ]);

    $fake->assertConverted(fn (RecordedConversion $conversion): bool =>
        $conversion->source === ConversionSource::Bytes
        && $conversion->format === Format::Csv
        && $conversion->input === 'bytes');

    $fake->assertConverted(fn (RecordedConversion $conversion): bool =>
        $conversion->source === ConversionSource::Disk
        && $conversion->disk === 'documents'
        && $conversion->input === 'report.docx');
});

it('records conversions only when an output is requested', function (): void {
    Anydoc::fake([
        'markdown' => '# Fake document',
    ]);

    $conversion = Anydoc::file('/documents/report.docx');

    Anydoc::assertNothingConverted();

    expect($conversion->markdown())
        ->toBe('# Fake document');

    Anydoc::assertConverted();
    Anydoc::assertConvertedTimes();
    Anydoc::assertConvertedToMarkdown();
    Anydoc::assertNotConverted(
        fn (RecordedConversion $recorded): bool => $recorded->output === ConversionOutput::Document,
    );
});

it('returns an explicitly configured document', function (): void {
    $document = anydoc_to_document("name,role\nAda,Engineer\n", 'csv');
    $fake = Anydoc::fake([
        'document' => $document,
    ]);

    expect(Anydoc::bytes('ignored', Format::Csv)->document())
        ->toBe($document);

    $fake->assertConvertedToDocument(
        fn (RecordedConversion $conversion): bool => $conversion->source === ConversionSource::Bytes,
    );
});

it('supports response callbacks', function (): void {
    Anydoc::fake([
        'markdown' => fn (RecordedConversion $conversion): string => '# '.$conversion->source->value,
    ]);

    expect(Anydoc::bytes('bytes')->markdown())
        ->toBe('# bytes');
});

it('swaps the facade for a fake', function (): void {
    $fake = Anydoc::fake();

    expect($fake)
        ->toBeInstanceOf(AnydocFake::class)
        ->toBe(app(HosmelQ\Anydoc\Laravel\Contracts\Anydoc::class));
});
