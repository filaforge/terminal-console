# Filaforge Terminal Console

A Filament v4 panel plugin that provides a browser-based terminal page backed by an allowlist of safe server commands.

## Requirements
- PHP >= 8.1
- Laravel 12 (illuminate/support ^12)
- Filament ^4.0

## Installation
- Install via Composer:
  - In a consuming app: `composer require filaforge/terminal-console`
  - In this monorepo, the root app already maps `plugins/*` as path repositories.
- The service provider is auto-discovered.

## Register the plugin in your panel
```php
use Filaforge\TerminalConsole\TerminalConsolePlugin;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(TerminalConsolePlugin::make());
}
```

## Usage
Open the “Terminal Console” page. On load, the terminal prints a welcome message and shows the prompt. Type an allowlisted command and press Enter.

## Notes
- The terminal uses xterm.js under the hood and enforces an allowlist for security.
- SPA navigation is handled; the terminal auto-initializes on first visit.

---
Package: `filaforge/terminal-console`## Filaforge Terminal Console

Run allowlisted console commands from a Filament page.

Usage:

```php
->plugin(\Filaforge\TerminalConsole\TerminalConsolePlugin::make())
```

Configure allowlist in `config/terminal.php` (published by the package).

