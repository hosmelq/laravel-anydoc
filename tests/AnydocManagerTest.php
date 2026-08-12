<?php

declare(strict_types=1);

use Anydoc\Document;
use Anydoc\Exception\UnsupportedException;
use HosmelQ\Anydoc\Laravel\AnydocManager;
use HosmelQ\Anydoc\Laravel\Enums\Format;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('preserves native conversion exceptions', function (): void {
    expect(fn (): string => new AnydocManager(app(Factory::class))->bytes('not a document')->markdown())
        ->toThrow(UnsupportedException::class);
});

it('reports unreadable local files with the Laravel filesystem exception', function (): void {
    expect(fn (): Document => new AnydocManager(app(Factory::class))->file(__DIR__.'/Fixtures/missing.csv')->document())
        ->toThrow(FileNotFoundException::class);
});

it('converts bytes to markdown and the document model', function (): void {
    $bytes = "name,role\nAda,Engineer\n";
    $conversion = new AnydocManager(app(Factory::class))->bytes($bytes, Format::Csv);

    expect($conversion->markdown())
        ->toContain('| name | role |')
        ->and($conversion->document())
        ->toBeInstanceOf(Document::class);
});

it('converts files from Laravel disks', function (): void {
    Storage::fake('documents');

    Storage::disk('documents')->put('people.csv', "name,role\nAda,Engineer\n");

    expect(new AnydocManager(app(Factory::class))->disk('documents')->file('people.csv')->markdown())
        ->toContain('| name | role |');
});

it('converts local files with extension fallback', function (): void {
    $conversion = new AnydocManager(app(Factory::class))->file(__DIR__.'/Fixtures/people.csv');

    expect($conversion->markdown())
        ->toContain('| name | role |')
        ->and($conversion->document())
        ->toBeInstanceOf(Document::class);
});

it('converts uploaded files using their original name', function (): void {
    $file = UploadedFile::fake()->createWithContent(
        'people.csv',
        "name,role\nAda,Engineer\n",
    );

    expect(new AnydocManager(app(Factory::class))->file($file)->markdown())
        ->toContain('| name | role |');
});

it('exposes all native format detection operations', function (): void {
    $anydoc = new AnydocManager(app(Factory::class));

    expect($anydoc->formatFromBytes('{\\rtf1 anydoc}'))
        ->toBe(Format::Rtf)
        ->and($anydoc->formatFromExtension('.PPTM'))
        ->toBe(Format::Pptx)
        ->and($anydoc->formatFromPath('documents/report.xls'))
        ->toBe(Format::Xlsx)
        ->and($anydoc->formatFromPath('documents/report.txt'))
        ->toBeNull();
});

it('maps every native format to the Laravel enum', function (string $extension, Format $format): void {
    expect(new AnydocManager(app(Factory::class))->formatFromExtension($extension))
        ->toBe($format);
})->with([
    'CSV' => ['csv', Format::Csv],
    'EPUB' => ['epub', Format::Epub],
    'Excel' => ['xlsx', Format::Xlsx],
    'OpenDocument presentation' => ['odp', Format::Odp],
    'OpenDocument spreadsheet' => ['ods', Format::Ods],
    'OpenDocument text' => ['odt', Format::Odt],
    'PDF' => ['pdf', Format::Pdf],
    'PowerPoint binary' => ['ppt', Format::Ppt],
    'PowerPoint Open XML' => ['pptx', Format::Pptx],
    'Rich Text Format' => ['rtf', Format::Rtf],
    'Word binary' => ['doc', Format::Doc],
    'Word Open XML' => ['docx', Format::Docx],
]);
