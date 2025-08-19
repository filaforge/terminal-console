<?php

namespace Filaforge\TerminalConsole;

use Filaforge\TerminalConsole\Pages\TerminalPage;
use Filaforge\TerminalConsole\Pages\TerminalSettingsPage;
use Filament\Contracts\Plugin;
use Filament\Panel;

class TerminalConsolePlugin implements Plugin
{
    public function getId(): string
    {
        return 'terminal-console';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            TerminalPage::class,
            TerminalSettingsPage::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }
}


