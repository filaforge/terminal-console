<?php

use Filaforge\TerminalConsole\TerminalConsole;

it('can instantiate terminal console', function () {
    $terminal = new TerminalConsole();
    
    expect($terminal)->toBeInstanceOf(TerminalConsole::class);
});

it('has correct version', function () {
    $terminal = new TerminalConsole();
    
    expect($terminal->getVersion())->toBe('2.0.0');
});

it('has correct name', function () {
    $terminal = new TerminalConsole();
    
    expect($terminal->getName())->toBe('Terminal Console');
});

it('can check if enabled', function () {
    config(['terminal.enabled' => true]);
    $terminal = new TerminalConsole();
    
    expect($terminal->isEnabled())->toBeTrue();
    
    config(['terminal.enabled' => false]);
    expect($terminal->isEnabled())->toBeFalse();
});

it('can get allowed commands', function () {
    config(['terminal.allowed_binaries' => ['ls', 'pwd', 'cat']]);
    $terminal = new TerminalConsole();
    
    expect($terminal->getAllowedCommands())->toBe(['ls', 'pwd', 'cat']);
});

it('can get presets', function () {
    config(['terminal.presets' => ['test' => ['command' => 'ls']]]);
    $terminal = new TerminalConsole();
    
    expect($terminal->getPresets())->toBe(['test' => ['command' => 'ls']]);
});

it('can get working directory', function () {
    config(['terminal.working_directory' => '/tmp']);
    $terminal = new TerminalConsole();
    
    expect($terminal->getWorkingDirectory())->toBe('/tmp');
});

it('can get timeout', function () {
    config(['terminal.timeout' => 120]);
    $terminal = new TerminalConsole();
    
    expect($terminal->getTimeout())->toBe(120);
});

it('can get max history', function () {
    config(['terminal.max_history' => 50]);
    $terminal = new TerminalConsole();
    
    expect($terminal->getMaxHistory())->toBe(50);
});

it('can check if logging is enabled', function () {
    config(['terminal.logging.enabled' => true]);
    $terminal = new TerminalConsole();
    
    expect($terminal->isLoggingEnabled())->toBeTrue();
    
    config(['terminal.logging.enabled' => false]);
    expect($terminal->isLoggingEnabled())->toBeFalse();
});

it('can get log channel', function () {
    config(['terminal.logging.channel' => 'custom']);
    $terminal = new TerminalConsole();
    
    expect($terminal->getLogChannel())->toBe('custom');
    
    config(['terminal.logging.channel' => null]);
    expect($terminal->getLogChannel())->toBeNull();
});
