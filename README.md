# Laravel anydoc

Convert documents to GitHub-Flavored Markdown in Laravel applications, with
access to [anydoc's](https://github.com/firecrawl/anydoc) structured document
model when available.

## Requirements

- ext-anydoc ^0.2.4
- Laravel 12+
- PHP 8.4+

## Installation

Install the native [extension](https://github.com/hosmelq/ext-anydoc) with
[PIE](https://php.github.io/pie/):

```bash
pie install hosmelq/ext-anydoc:^0.2.4
```

Install Laravel anydoc with Composer:

```bash
composer require hosmelq/laravel-anydoc
```

## Convert documents

Convert a local file to Markdown with the `Anydoc` facade:

```php
use HosmelQ\Anydoc\Laravel\Facades\Anydoc;

$markdown = Anydoc::file('report.docx')->markdown();
```

`file()` also accepts an uploaded file:

```php
$markdown = Anydoc::file($uploadedFile)->markdown();
```

Convert document bytes directly:

```php
$bytes = file_get_contents('report.docx');

$markdown = Anydoc::bytes($bytes)->markdown();
```

CSV has no content signature, so CSV bytes require an explicit format:

```php
use HosmelQ\Anydoc\Laravel\Enums\Format;
use HosmelQ\Anydoc\Laravel\Facades\Anydoc;

$bytes = file_get_contents('data.csv');

$markdown = Anydoc::bytes($bytes, Format::Csv)->markdown();
```

All conversions run synchronously.

## Convert files from disks

Convert a file stored on a Laravel filesystem disk:

```php
use HosmelQ\Anydoc\Laravel\Facades\Anydoc;

$markdown = Anydoc::disk('s3')
    ->file('documents/report.docx')
    ->markdown();
```

Call `Anydoc::disk()` without a name to use Laravel's default disk.

Disk files and uploaded files are read into memory before conversion.

## Read structured documents

Call `document()` to access anydoc's readonly document model:

```php
use HosmelQ\Anydoc\Laravel\Facades\Anydoc;

$document = Anydoc::file('presentation.pptx')->document();

$assets = $document->assets;
$blocks = $document->blocks;
$notes = $document->notes;
```

The document model includes blocks, checkboxes, embedded assets, inline content,
lists, math, notes, and tables.

PDF supports Markdown conversion only. Calling `document()` for a PDF throws an
`Anydoc\Exception\UnsupportedException`.

## Supported formats

| Format | Extensions |
| --- | --- |
| CSV | `.csv` |
| EPUB | `.epub` |
| Excel | `.xls`, `.xlsb`, `.xlsm`, `.xlsx` |
| OpenDocument | `.odp`, `.ods`, `.odt` |
| PDF | `.pdf` |
| PowerPoint | `.pot`, `.pps`, `.ppsm`, `.ppsx`, `.ppt`, `.pptm`, `.pptx` |
| Rich Text Format | `.rtf` |
| Word | `.doc`, `.docm`, `.docx` |

Pass a `Format` enum case when the format cannot be detected from the content:

```php
use HosmelQ\Anydoc\Laravel\Enums\Format;
use HosmelQ\Anydoc\Laravel\Facades\Anydoc;

$markdown = Anydoc::bytes($bytes, Format::Docx)->markdown();
```

## Detect formats

Detect a format from bytes, an extension, or a path:

```php
use HosmelQ\Anydoc\Laravel\Facades\Anydoc;

$fromBytes = Anydoc::formatFromBytes($bytes);
$fromExtension = Anydoc::formatFromExtension('.DOCX');
$fromPath = Anydoc::formatFromPath('documents/report.docx');
```

Each method returns a `Format` enum case or `null`. Extension detection is
case-insensitive and accepts an optional leading dot.

Uploaded files use their contents first and their original name as a fallback.
Filesystem files use their contents first and their path as a fallback.

## Use dependency injection

Inject the `Anydoc` contract when a class should not depend on the facade:

```php
use HosmelQ\Anydoc\Laravel\Contracts\Anydoc;

final class ConvertDocument
{
    public function __construct(private Anydoc $anydoc) {}

    public function handle(string $path): string
    {
        return $this->anydoc->file($path)->markdown();
    }
}
```

## Handle errors

Native conversion errors extend `Anydoc\Exception\ConvertException`:

```php
use Anydoc\Exception\ConvertException;
use Anydoc\Exception\PanicException;
use HosmelQ\Anydoc\Laravel\Facades\Anydoc;

try {
    $markdown = Anydoc::file('report.docx')->markdown();
} catch (ConvertException $exception) {
    report($exception);
} catch (PanicException $exception) {
    report($exception);
}
```

Conversion exceptions include `EncryptedException`, `IoException`,
`MalformedException`, `MissingPartException`, `NeedsOcrException`,
`ResourceLimitException`, and `UnsupportedException`. `NeedsOcrException`
provides the 1-indexed PDF pages requiring OCR through `$pages` and the total
number of pages through `$pageCount`. Filesystem sources may also throw Laravel
filesystem exceptions when their contents cannot be read.

`PanicException` represents a panic from the native Rust library and does not
extend `ConvertException`.

## Test conversions

Call `Anydoc::fake()` to test application behavior without reading files or
running conversions:

```php
use HosmelQ\Anydoc\Laravel\Facades\Anydoc;
use HosmelQ\Anydoc\Laravel\RecordedConversion;

Anydoc::fake([
    'markdown' => '# Converted document',
]);

Anydoc::file('report.docx')->markdown();

Anydoc::assertConvertedToMarkdown(
    fn (RecordedConversion $conversion): bool => $conversion->input === 'report.docx',
);
```

Use a closure when the response depends on the recorded conversion:

```php
Anydoc::fake([
    'markdown' => fn (RecordedConversion $conversion): string => "# {$conversion->input}",
]);

$markdown = Anydoc::file('report.docx')->markdown();
```

The fake provides assertions and access to recorded conversions:

```php
Anydoc::assertConverted();
Anydoc::assertConvertedTimes(2);
Anydoc::assertConvertedToDocument();
Anydoc::assertConvertedToMarkdown();
Anydoc::assertNotConverted($callback);
Anydoc::assertNothingConverted();

$conversions = Anydoc::conversions();
```

Assertion and filtering callbacks receive a `RecordedConversion` containing
its disk, format, input, output type, and source type. A conversion is recorded
when `markdown()` or `document()` is called.

The default fake Markdown response is an empty string. Document conversions
require a configured `Anydoc\Document` response.

## Development

Run the test suite with:

```bash
composer test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes.

## Contributing

Pull requests are welcome. Please run the test suite before submitting changes.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
