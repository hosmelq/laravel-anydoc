<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel\Exceptions;

use Illuminate\Http\Client\ConnectionException;
use RuntimeException;

final class HostedOcrException extends RuntimeException
{
    public static function connectionFailed(ConnectionException $connectionException): self
    {
        return new self('Firecrawl Parse: '.$connectionException->getMessage(), 0, $connectionException);
    }

    public static function invalidApiKey(string $detail): self
    {
        return new self('Firecrawl Parse rejected the API key: '.$detail, 401);
    }

    public static function keylessLimitReached(string $detail): self
    {
        return new self('Firecrawl Parse keyless limit reached, set FIRECRAWL_API_KEY: '.$detail, 429);
    }

    public static function missingMarkdown(): self
    {
        return new self('Firecrawl Parse returned no Markdown');
    }

    public static function outOfCredits(string $detail): self
    {
        return new self('Firecrawl Parse is out of credits: '.$detail, 402);
    }

    public static function rateLimitReached(string $detail): self
    {
        return new self('Firecrawl Parse rate limit reached: '.$detail, 429);
    }

    public static function requestFailed(int $status, string $detail): self
    {
        return new self('Firecrawl Parse: '.$detail, $status);
    }
}
