<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel;

use Anydoc\Document;

abstract readonly class PendingConversion
{
    abstract public function document(): Document;

    abstract public function markdown(): string;
}
