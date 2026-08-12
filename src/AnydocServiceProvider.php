<?php

declare(strict_types=1);

namespace HosmelQ\Anydoc\Laravel;

use HosmelQ\Anydoc\Laravel\Contracts\Anydoc;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AnydocServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('anydoc');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Anydoc::class, AnydocManager::class);
    }
}
