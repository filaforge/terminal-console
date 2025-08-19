<?php

namespace Filaforge\TerminalConsole\Pages;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class MinimalTerminalPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-command-line';

    protected static ?string $navigationLabel = 'Terminal Console';

    protected static ?string $title = 'Terminal Console';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected string $view = 'terminal-console::pages.terminal';

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('command')
                    ->label('Command')
                    ->placeholder('Enter a command...')
                    ->rows(3),
            ]);
    }
}
