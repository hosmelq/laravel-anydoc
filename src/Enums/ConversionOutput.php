<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel\Enums;

enum ConversionOutput: string
{
    case Document = 'document';
    case Markdown = 'markdown';
}
