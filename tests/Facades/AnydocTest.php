<?php

declare(strict_types=1);

use HosmelQ\Anydoc\Laravel\Enums\Format;
use HosmelQ\Anydoc\Laravel\Facades\Anydoc;

it('converts documents through the facade', function (): void {
    expect(Anydoc::bytes("name,role\nAda,Engineer\n", Format::Csv)->markdown())
        ->toContain('| name | role |')
        ->and(Anydoc::formatFromExtension('.docm'))
        ->toBe(Format::Docx);
});
