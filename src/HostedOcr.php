<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel;

use function Safe\json_encode;

use HosmelQ\Anydoc\Laravel\Exceptions\HostedOcrException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

final class HostedOcr
{
    public function convert(string $bytes, string $filename = 'document.pdf'): string
    {
        $apiKey = Config::string('anydoc.ocr.api_key');
        $apiUrl = Config::string('anydoc.ocr.api_url');

        $request = Http::acceptJson()
            ->timeout(Config::integer('anydoc.ocr.timeout'))
            ->when($apiKey !== '', fn (PendingRequest $request): PendingRequest => $request->withToken($apiKey))
            ->attach('file', $bytes, $filename, ['Content-Type' => 'application/pdf']);

        try {
            $response = $request->post(mb_rtrim($apiUrl, '/').'/v2/parse', [
                'options' => json_encode([
                    'parsers' => [['type' => 'pdf', 'mode' => 'auto']],
                    'origin' => 'laravel-anydoc',
                ]),
            ]);
        } catch (ConnectionException $connectionException) {
            throw HostedOcrException::connectionFailed($connectionException);
        }

        if (! $response->successful() || $response->json('success', null, 0) !== true) {
            $detail = $response->json('error', null, 0);
            $detail = is_string($detail) && $detail !== '' ? $detail : 'HTTP '.$response->status();

            throw match ($response->status()) {
                401 => HostedOcrException::invalidApiKey($detail),
                402 => HostedOcrException::outOfCredits($detail),
                429 => $apiKey === ''
                    ? HostedOcrException::keylessLimitReached($detail)
                    : HostedOcrException::rateLimitReached($detail),
                default => HostedOcrException::requestFailed($response->status(), $detail),
            };
        }

        $markdown = $response->json('data.markdown', null, 0);

        if (! is_string($markdown) || $markdown === '') {
            throw HostedOcrException::missingMarkdown();
        }

        return str_ends_with($markdown, "\n") ? $markdown : $markdown."\n";
    }
}
