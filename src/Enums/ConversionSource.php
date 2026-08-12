<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel\Enums;

enum ConversionSource: string
{
    case Bytes = 'bytes';
    case Disk = 'disk';
    case File = 'file';
    case Upload = 'upload';
}
