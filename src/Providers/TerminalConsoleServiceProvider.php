<?php

namespace Filaforge\TerminalConsole\Providers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TerminalConsoleServiceProvider extends PackageServiceProvider
{
    public static string $name = 'terminal-console';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile('terminal')
            ->hasViews();
    }
}


