<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->laravel();
arch()->preset()->security();

arch('strict types')
    ->expect('HosmelQ\Anydoc\Laravel')
    ->toUseStrictTypes();
