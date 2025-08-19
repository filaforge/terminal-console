<?php

namespace Filaforge\TerminalConsole\Pages;

use Filament\Pages\Page;

class SimpleTerminalPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-command-line';

    protected static ?string $navigationLabel = 'Terminal Console';

    protected static ?string $title = 'Terminal Console';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected string $view = 'terminal-console::pages.terminal';
}
