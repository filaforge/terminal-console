<?php

namespace Filaforge\TerminalConsole\Pages;

use Filaforge\TerminalConsole\Models\TerminalSetting;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class TerminalSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Terminal Settings';
    protected static ?string $title = 'Terminal Settings';
    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected string $view = 'terminal-console::pages.settings';

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'extra_path' => TerminalSetting::get('extra_path', ''),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('extra_path')
                    ->label('Extra PATH entries')
                    ->rows(3)
                    ->placeholder('/usr/local/sbin:/usr/local/bin:/usr/bin:/bin')
                    ->hint('These paths are prepended to PATH at runtime for terminal commands.')
                    ->helperText('Use colon-separated list. Empty to disable.'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        TerminalSetting::set('extra_path', $state['extra_path'] ?? '');

        Notification::make()
            ->title('Terminal settings saved')
            ->success()
            ->send();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return config('terminal.enabled', true);
    }

    public function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Save')
                ->submit('save')
                ->color('primary')
                ->icon('heroicon-o-check'),
        ];
    }
}
