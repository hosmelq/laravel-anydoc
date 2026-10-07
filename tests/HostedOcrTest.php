<?php

declare(strict_types=1);

use function Safe\file_get_contents;

use Anydoc\Exception\NeedsOcrException;
use Anydoc\Exception\UnsupportedException;
use HosmelQ\Anydoc\Laravel\Enums\Format;
use HosmelQ\Anydoc\Laravel\Exceptions\HostedOcrException;
use HosmelQ\Anydoc\Laravel\Facades\Anydoc;
use HosmelQ\Anydoc\Laravel\PendingConversion;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

it('does not send other native conversion failures to OCR', function (): void {
    expect(fn (): string => Anydoc::bytes('not a document')->markdown(ocr: true))
        ->toThrow(UnsupportedException::class);
});

it('preserves the cause of hosted connection failures', function (): void {
    Http::fake(['*' => Http::failedConnection('Connection timed out')]);

    expect(fn (): string => Anydoc::bytes(file_get_contents(__DIR__.'/Fixtures/scanned.pdf'))->markdown(ocr: true))
        ->toThrow(function (HostedOcrException $hostedOcrException): void {
            expect($hostedOcrException->getPrevious())->toBeInstanceOf(ConnectionException::class)
                ->and($hostedOcrException->getMessage())->toContain('Connection timed out');
        });
});

it('reports hosted conversion failures', function (array|string $body, int $status, string $message): void {
    Http::fake(['*' => Http::response($body, $status)]);

    expect(fn (): string => Anydoc::file(__DIR__.'/Fixtures/scanned.pdf')->markdown(ocr: true))
        ->toThrow(HostedOcrException::class, $message);
})->with([
    'http error' => ['<html>Server error</html>', 500, 'HTTP 500'],
    'missing markdown' => [['success' => true], 200, 'returned no Markdown'],
    'rejected conversion' => [['success' => false, 'error' => 'Cannot parse'], 200, 'Cannot parse'],
]);

it('reports the keyless OCR limit', function (): void {
    Http::fake(['*' => Http::response(['success' => false, 'error' => 'Too many requests'], 429)]);

    expect(fn (): string => Anydoc::file(__DIR__.'/Fixtures/scanned.pdf')->markdown(ocr: true))
        ->toThrow(HostedOcrException::class, 'set FIRECRAWL_API_KEY');
});

it('requires OCR opt-in for scanned PDFs', function (PendingConversion $conversion): void {
    expect(fn (): string => $conversion->markdown())->toThrow(NeedsOcrException::class);
})->with([
    'bytes' => fn (): PendingConversion => Anydoc::bytes(file_get_contents(__DIR__.'/Fixtures/scanned.pdf')),
    'file' => fn (): PendingConversion => Anydoc::file(__DIR__.'/Fixtures/scanned.pdf'),
]);

it('converts locally when OCR is not needed', function (PendingConversion $conversion): void {
    expect($conversion->markdown(ocr: true))
        ->toContain('| name | role |');
})->with([
    'bytes' => fn (): PendingConversion => Anydoc::bytes("name,role\nAda,Engineer\n", Format::Csv),
    'file' => fn (): PendingConversion => Anydoc::file(__DIR__.'/Fixtures/people.csv'),
]);

it('converts scanned PDFs with hosted OCR', function (PendingConversion $conversion, string $filename): void {
    Config::set([
        'anydoc.ocr.api_key' => 'test-key',
        'anydoc.ocr.api_url' => 'https://parse.example.test/',
    ]);

    Http::fake([
        '*' => Http::response([
            'success' => true,
            'data' => ['markdown' => '# Scanned document'],
        ]),
    ]);

    expect($conversion->markdown(ocr: true))->toBe("# Scanned document\n");

    Http::assertSent(fn (Request $request): bool =>
        $request->method() === 'POST'
        && $request->url() === 'https://parse.example.test/v2/parse'
        && $request->hasHeader('Authorization', 'Bearer test-key')
        && $request->hasFile('file', file_get_contents(__DIR__.'/Fixtures/scanned.pdf'), $filename));
    Http::assertSentCount(1);
})->with([
    'bytes' => [fn (): PendingConversion => Anydoc::bytes(file_get_contents(__DIR__.'/Fixtures/scanned.pdf')), 'document.pdf'],
    'file' => [fn (): PendingConversion => Anydoc::file(__DIR__.'/Fixtures/scanned.pdf'), 'scanned.pdf'],
]);
