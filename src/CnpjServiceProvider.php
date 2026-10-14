<?php

namespace JeffersonGoncalves\Cnpj;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CnpjServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-cnpj')
            ->hasConfigFile()
            ->hasMigration('create_cnpj_table');
    }
}
