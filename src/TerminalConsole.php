<?php

namespace Filaforge\TerminalConsole;

class TerminalConsole
{
    public function getVersion(): string
    {
        return '2.0.0';
    }

    public function getName(): string
    {
        return 'Terminal Console';
    }

    public function getDescription(): string
    {
        return 'A powerful Filament panel plugin that provides a browser-based terminal console with command presets, security allowlists, and real-time execution capabilities.';
    }

    public function isEnabled(): bool
    {
        return config('terminal.enabled', true);
    }

    public function getAllowedCommands(): array
    {
        return config('terminal.allowed_binaries', []);
    }

    public function getPresets(): array
    {
        return config('terminal.presets', []);
    }

    public function getWorkingDirectory(): string
    {
        return config('terminal.working_directory', base_path());
    }

    public function getTimeout(): int
    {
        return config('terminal.timeout', 60);
    }

    public function getMaxHistory(): int
    {
        return config('terminal.max_history', 100);
    }

    public function isLoggingEnabled(): bool
    {
        return config('terminal.logging.enabled', true);
    }

    public function getLogChannel(): ?string
    {
        return config('terminal.logging.channel');
    }
}
