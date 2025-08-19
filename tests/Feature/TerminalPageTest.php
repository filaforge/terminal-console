<?php

use Filaforge\TerminalConsole\Pages\TerminalPage;
use Filament\Livewire\Livewire;

it('can render terminal page', function () {
    config(['terminal.enabled' => true]);
    
    Livewire::test(TerminalPage::class)
        ->assertSuccessful();
});

it('validates command length', function () {
    config(['terminal.enabled' => true]);
    
    $longCommand = str_repeat('a', 1001);
    
    Livewire::test(TerminalPage::class)
        ->fillForm(['command' => $longCommand])
        ->call('run')
        ->assertNotified('Command too long');
});

it('blocks dangerous commands', function () {
    config(['terminal.enabled' => true]);
    
    $dangerousCommands = [
        'rm -rf /',
        'rm -rf *',
        'mkfs /dev/sda1',
        'dd if=/dev/zero',
        ':(){:|:&};:',
    ];
    
    foreach ($dangerousCommands as $command) {
        Livewire::test(TerminalPage::class)
            ->fillForm(['command' => $command])
            ->call('run')
            ->assertNotified('Dangerous command detected');
    }
});

it('respects command allowlist', function () {
    config([
        'terminal.enabled' => true,
        'terminal.allow_any' => false,
        'terminal.allowed_binaries' => ['ls', 'pwd'],
    ]);
    
    // Allowed command should work
    Livewire::test(TerminalPage::class)
        ->fillForm(['command' => 'ls'])
        ->call('run')
        ->assertNotNotified('Command not allowed');
    
    // Disallowed command should be blocked
    Livewire::test(TerminalPage::class)
        ->fillForm(['command' => 'cat /etc/passwd'])
        ->call('run')
        ->assertNotified('Command not allowed');
});

it('handles cd command', function () {
    config(['terminal.enabled' => true]);
    
    Livewire::test(TerminalPage::class)
        ->fillForm(['command' => 'cd /tmp'])
        ->call('run')
        ->assertNotified('Directory changed');
});

it('handles clear command', function () {
    config(['terminal.enabled' => true]);
    
    Livewire::test(TerminalPage::class)
        ->fillForm(['command' => 'clear'])
        ->call('run')
        ->assertDispatched('terminal.clear');
});

it('can execute preset commands', function () {
    config([
        'terminal.enabled' => true,
        'terminal.presets' => [
            'Test' => [
                [
                    'label' => 'List Files',
                    'command' => 'ls -la',
                    'description' => 'List all files',
                    'icon' => 'heroicon-o-folder',
                    'color' => 'primary',
                ],
            ],
        ],
    ]);
    
    Livewire::test(TerminalPage::class)
        ->call('executePreset', 'ls -la')
        ->assertSet('data.command', 'ls -la');
});

it('provides tab completion for commands', function () {
    config([
        'terminal.enabled' => true,
        'terminal.allowed_binaries' => ['ls', 'cat', 'pwd'],
        'terminal.tab_completion' => true,
    ]);
    
    $suggestions = Livewire::test(TerminalPage::class)
        ->call('getTabCompletion', 'l')
        ->getReturnValue();
    
    expect($suggestions)->toContain('ls');
});

it('respects rate limiting', function () {
    config([
        'terminal.enabled' => true,
        'terminal.rate_limit' => 1,
    ]);
    
    $component = Livewire::test(TerminalPage::class)
        ->fillForm(['command' => 'pwd'])
        ->call('run');
    
    // Second call should be rate limited
    $component
        ->fillForm(['command' => 'pwd'])
        ->call('run')
        ->assertNotified('Rate limit exceeded');
});
