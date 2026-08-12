<?php

declare(strict_types=1);

use HosmelQ\Anydoc\Laravel\AnydocManager;
use HosmelQ\Anydoc\Laravel\Contracts\Anydoc;

it('registers the anydoc contract as a singleton', function (): void {
    $manager = $this->app->make(Anydoc::class);

    expect($manager)
        ->toBeInstanceOf(AnydocManager::class)
        ->toBe($this->app->make(Anydoc::class));
});
