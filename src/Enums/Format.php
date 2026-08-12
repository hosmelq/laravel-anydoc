<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel\Enums;

enum Format: string
{
    case Csv = 'csv';
    case Doc = 'doc';
    case Docx = 'docx';
    case Epub = 'epub';
    case Odp = 'odp';
    case Ods = 'ods';
    case Odt = 'odt';
    case Pdf = 'pdf';
    case Ppt = 'ppt';
    case Pptx = 'pptx';
    case Rtf = 'rtf';
    case Xlsx = 'xlsx';
}
