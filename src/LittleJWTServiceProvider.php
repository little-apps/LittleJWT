<?php

namespace LittleApps\LittleJWT;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use LittleApps\LittleJWT\Commands\LittleJWTCommand;

class LittleJWTServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('littlejwt')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_littlejwt_table')
            ->hasCommand(LittleJWTCommand::class);
    }
}
