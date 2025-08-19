<?php

namespace Filaforge\TerminalConsole\Providers;

use Filaforge\TerminalConsole\Commands\TerminalCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TerminalConsoleServiceProvider extends PackageServiceProvider
{
    public static string $name = 'terminal-console';

    public static string $viewNamespace = 'terminal-console';

    public function configurePackage(Package $package): void
    {
        // Use non-chained calls to avoid any potential parse issues
        $package->name(static::$name);
        $package->hasConfigFile('terminal');
        $package->hasMigrations([
            'create_terminal_console_settings_table',
        ]);
        $package->hasViews();
        $package->hasAssets();
        $package->hasTranslations();
        $package->hasCommands([
            TerminalCommand::class,
        ]);
    }

    public function packageRegistered(): void
    {
        parent::packageRegistered();

        $this->app->scoped('terminal-console', function () {
            return new \Filaforge\TerminalConsole\TerminalConsole();
        });
    }

    public function packageBooted(): void
    {
        parent::packageBooted();

        // Publish groups
        $this->publishes([
            __DIR__ . '/../../config/terminal.php' => config_path('terminal.php'),
        ], 'terminal-console-config');

        $this->publishes([
            __DIR__ . '/../../resources' => resource_path('vendor/terminal-console'),
        ], 'terminal-console-resources');

        $this->publishes([
            __DIR__ . '/../../resources/js' => resource_path('vendor/terminal-console/js'),
            __DIR__ . '/../../resources/css' => resource_path('vendor/terminal-console/css'),
        ], 'terminal-console-assets');

        // Publish web assets directly to public for easy linking
        $this->publishes([
            __DIR__ . '/../../resources/js' => public_path('vendor/terminal-console/js'),
            __DIR__ . '/../../resources/css' => public_path('vendor/terminal-console/css'),
        ], 'terminal-console-public');
    }
}