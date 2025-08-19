<?php

namespace Filaforge\TerminalConsole\Pages;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class TerminalPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-command-line';

    protected static ?string $navigationLabel = 'Terminal Console';

    protected static ?string $title = 'Terminal Console';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected string $view = 'terminal-console::pages.terminal';

    public array $data = [];

    /**
     * @var list<array{command:string, exit:int|null, output:string, timestamp:string}>
     */
    public array $history = [];

    public ?int $exitCode = null;

    public string $currentWorkingDirectory = '';

    public array $commandHistory = [];

    public int $historyIndex = -1;

    public function mount(): void
    {
        if (!config('terminal.enabled', true)) {
            abort(403, 'Terminal console is disabled.');
        }
        $this->updateCurrentWorkingDirectory();
        $this->loadCommandHistory();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('command')
                    ->label('Command')
                    ->placeholder('Enter command (e.g., php artisan migrate)')
                    ->required()
                    ->autocomplete(false)
                    ->extraAlpineAttributes([
                        'x-on:keydown.ctrl.enter.prevent' => '$wire.run()',
                        'x-on:keydown.enter.prevent' => '$wire.run()',
                    ]),
                
                Textarea::make('output')
                    ->rows(1)
                    ->disabled()
                    ->dehydrated(false)
                    ->hidden(),
            ])
            ->statePath('data');
    }

    // Preset components are rendered in the Blade view; no form actions needed.

    public function run(): void
    {
        // Rate limiting
        $rateLimitKey = 'terminal-commands:' . auth()->id();
        $maxAttempts = config('terminal.rate_limit', 60);
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            Notification::make()
                ->title('Rate limit exceeded')
                ->body("Too many commands. Try again in {$seconds} seconds.")
                ->danger()
                ->send();
            return;
        }

        RateLimiter::hit($rateLimitKey, 60); // 1 minute window

        $state = $this->form->getState();
        $full = trim((string) ($state['command'] ?? ''));

        if ($full === '') {
            Notification::make()->title('Command is required')->danger()->send();
            return;
        }

        // Validate and sanitize input
        if (!$this->validateCommand($full)) {
            return;
        }

        [$binary, $args] = $this->splitBinaryAndArgs($full);

        // Log command execution attempt
        $this->logCommand($full, 'attempt');

        // Handle shell built-ins like `cd` without spawning a process
        if ($binary === 'cd') {
            $this->handleCdCommand($args);
            $this->exitCode = 0;

            // Append to history
            $this->addToHistory($full, 0, '');
            $this->addToCommandHistory($full);

            $this->clearInput();
            $this->notifyTerminal($full, '', 0);
            
            Notification::make()->title('Directory changed')->success()->send();
            return;
        }

        // Built-in `clear` / `cls` command
        if (in_array($binary, ['clear', 'cls'], true)) {
            $this->exitCode = 0;
            $this->addToHistory($full, 0, '');
            $this->addToCommandHistory($full);
            $this->clearInput();
            
            try {
                $this->dispatch('terminal.clear', path: $this->getCurrentPath());
            } catch (\Throwable $e) {
                // ignore
            }

            return;
        }

        // Security: enforce allowlist
        if (!$this->isCommandAllowed($binary)) {
            $this->logCommand($full, 'blocked');
            Notification::make()
                ->title('Command not allowed')
                ->body("The command '{$binary}' is blocked or not permitted by configuration.")
                ->danger()
                ->send();
            return;
        }

        // Execute the command
        $this->executeCommand($full, $binary, $args);
    }

    protected function validateCommand(string $command): bool
    {
        // Basic input validation
        if (strlen($command) > 1000) {
            Notification::make()
                ->title('Command too long')
                ->body('Command must be less than 1000 characters.')
                ->danger()
                ->send();
            return false;
        }

        // Check for dangerous patterns
        $dangerousPatterns = [
            '/\s*(rm\s+-rf\s+\/|\srm\s+-rf\s+\*)/i',
            '/\s*mkfs\s+/i',
            '/\s*dd\s+if=/i',
            '/\s*:\(\)\{\s*:\|\:&\s*\}\s*;:\s*/i', // fork bomb
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $command)) {
                Notification::make()
                    ->title('Dangerous command detected')
                    ->body('This command pattern is not allowed for security reasons.')
                    ->danger()
                    ->send();
                return false;
            }
        }

        return true;
    }

    protected function isCommandAllowed(string $binary): bool
    {
        $blocked = (array) config('terminal.blocked_binaries', []);
        if (in_array($binary, $blocked, true)) {
            return false;
        }

        $allowAny = (bool) config('terminal.allow_any', false);
        if ($allowAny) {
            return true;
        }

        $allowedBinaries = (array) config('terminal.allowed_binaries', []);
        return in_array($binary, $allowedBinaries, true);
    }

    protected function executeCommand(string $full, string $binary, array $args): void
    {
        $workingDir = !empty($this->currentWorkingDirectory) 
            ? $this->currentWorkingDirectory 
            : config('terminal.working_directory', base_path());

        $process = new Process(
            array_merge([$binary], $args), 
            $workingDir, 
            $this->getEnvironmentVariables()
        );
        
        $process->setTimeout(config('terminal.timeout', 60));

        try {
            $process->run();
            $this->exitCode = $process->getExitCode();
            $output = $process->getOutput() . ($process->getErrorOutput() ? "\n" . $process->getErrorOutput() : '');
            
            $this->logCommand($full, $this->exitCode === 0 ? 'success' : 'failed', $output);
        } catch (\Throwable $e) {
            $this->exitCode = 1;
            $output = $e->getMessage();
            $this->logCommand($full, 'error', $output);
        }

        // Append to history
        $this->addToHistory($full, $this->exitCode, $output);
        $this->addToCommandHistory($full);
        $this->clearInput();
        $this->notifyTerminal($full, $output, $this->exitCode);

        if ($this->exitCode === 0) {
            Notification::make()->title('Command executed successfully')->success()->send();
        } else {
            Notification::make()->title('Command failed')->danger()->send();
        }
    }

    protected function getEnvironmentVariables(): array
    {
    $defaultEnv = $_ENV;
    $customEnv = config('terminal.environment_variables', []);

    // Merge PATH with optional EXTRA PATH (DB or .env) without overriding
    $env = array_merge($defaultEnv, $customEnv);
    $extraPath = \Filaforge\TerminalConsole\Models\TerminalSetting::get('extra_path') ?? env('TERMINAL_EXTRA_PATH');
        if (is_string($extraPath) && $extraPath !== '') {
            $currentPath = $env['PATH'] ?? getenv('PATH') ?? '';
            // Prepend extra PATH so higher priority dirs come first
            $mergedPath = $extraPath . ($currentPath ? (PATH_SEPARATOR . $currentPath) : '');
            $env['PATH'] = $mergedPath;
        }

        return $env;
    }

    protected function logCommand(string $command, string $status, string $output = ''): void
    {
        if (!config('terminal.logging.enabled', true)) {
            return;
        }

        $shouldLog = match ($status) {
            'success' => config('terminal.logging.log_successful', true),
            'failed', 'error' => config('terminal.logging.log_failed', true),
            default => true,
        };

        if (!$shouldLog) {
            return;
        }

        $logData = [
            'user_id' => auth()->id(),
            'command' => $command,
            'status' => $status,
            'working_directory' => $this->currentWorkingDirectory,
            'timestamp' => now()->toISOString(),
        ];

        if (config('terminal.logging.include_output', false) && $output) {
            $logData['output'] = Str::limit($output, 500);
        }

        $channel = config('terminal.logging.channel');
        $logger = $channel ? Log::channel($channel) : Log::getLogger();

        match ($status) {
            'success' => $logger->info('Terminal command executed', $logData),
            'failed', 'error' => $logger->warning('Terminal command failed', $logData),
            'blocked' => $logger->warning('Terminal command blocked', $logData),
            default => $logger->info('Terminal command attempted', $logData),
        };
    }

    protected function addToHistory(string $command, ?int $exitCode, string $output): void
    {
        $this->history[] = [
            'command' => $command,
            'exit' => $exitCode,
            'output' => $output,
            'timestamp' => now()->format('H:i:s'),
        ];

        // Trim history
        $max = config('terminal.max_history', 100);
        if (count($this->history) > $max) {
            $this->history = array_slice($this->history, -$max);
        }
    }

    protected function clearInput(): void
    {
        $this->form->fill([
            'command' => '',
            'output' => '',
        ]);
    }

    protected function notifyTerminal(string $command, string $output, ?int $exitCode): void
    {
        try {
            $this->dispatch('terminal.output',
                command: $command,
                output: $output,
                exit: $exitCode,
                path: $this->getCurrentPath()
            );
        } catch (\Throwable $e) {
            // Ignore dispatch errors to avoid breaking UX
        }
    }

    private function handleCdCommand(array $args): void
    {
        if (empty($args)) {
            // cd with no arguments goes to home
            $home = getenv('HOME') ?: '/home/' . get_current_user();
            $this->currentWorkingDirectory = $home;
        } else {
            $targetDir = $args[0];

            // Handle relative and absolute paths
            if ($targetDir === '..') {
                $this->currentWorkingDirectory = dirname($this->currentWorkingDirectory);
            } elseif ($targetDir === '.') {
                // Stay in current directory
            } elseif ($targetDir === '~') {
                $home = getenv('HOME') ?: '/home/' . get_current_user();
                $this->currentWorkingDirectory = $home;
            } elseif (str_starts_with($targetDir, '/')) {
                // Absolute path
                if (is_dir($targetDir)) {
                    $this->currentWorkingDirectory = realpath($targetDir) ?: $targetDir;
                }
            } elseif (str_starts_with($targetDir, '~/')) {
                // Home relative path
                $home = getenv('HOME') ?: '/home/' . get_current_user();
                $fullPath = $home . '/' . substr($targetDir, 2);
                if (is_dir($fullPath)) {
                    $this->currentWorkingDirectory = realpath($fullPath) ?: $fullPath;
                }
            } else {
                // Relative path
                $fullPath = $this->currentWorkingDirectory . '/' . $targetDir;
                if (is_dir($fullPath)) {
                    $this->currentWorkingDirectory = realpath($fullPath) ?: $fullPath;
                }
            }
        }
    }

    private function addToCommandHistory(string $command): void
    {
        // Don't add empty commands or duplicates
        if (empty(trim($command)) || (end($this->commandHistory) === $command)) {
            return;
        }

        $this->commandHistory[] = $command;

        // Limit history size
        $maxHistory = config('terminal.max_history', 100);
        if (count($this->commandHistory) > $maxHistory) {
            $this->commandHistory = array_slice($this->commandHistory, -$maxHistory);
        }

        // Reset history index
        $this->historyIndex = -1;

        // Save to session
        $this->saveCommandHistory();
    }

    private function loadCommandHistory(): void
    {
        if (!config('terminal.command_history', true)) {
            return;
        }
        
        $this->commandHistory = session('terminal_command_history', []);
    }

    private function saveCommandHistory(): void
    {
        if (!config('terminal.command_history', true)) {
            return;
        }
        
        session(['terminal_command_history' => $this->commandHistory]);
    }

    public function getHistoryCommand(string $direction): string
    {
        if (empty($this->commandHistory)) {
            return '';
        }

        if ($direction === 'up') {
            if ($this->historyIndex === -1) {
                $this->historyIndex = count($this->commandHistory) - 1;
            } elseif ($this->historyIndex > 0) {
                $this->historyIndex--;
            }
        } elseif ($direction === 'down') {
            if ($this->historyIndex < count($this->commandHistory) - 1 && $this->historyIndex >= 0) {
                $this->historyIndex++;
            } else {
                $this->historyIndex = -1;
                return '';
            }
        }

        return $this->historyIndex >= 0 ? $this->commandHistory[$this->historyIndex] : '';
    }

    public function getTabCompletion(string $partialCommand): array
    {
        if (!config('terminal.tab_completion', true)) {
            return [];
        }

        $parts = explode(' ', $partialCommand);
        $lastPart = end($parts);
        $suggestions = [];

        // Command completion for first word
        if (count($parts) === 1) {
            $allowedCommands = config('terminal.allowed_binaries', []);
            $blocked = (array) config('terminal.blocked_binaries', []);
            $builtinCommands = ['cd', 'clear', 'cls'];
            $commands = array_merge($allowedCommands, $builtinCommands);
            // Filter blocked from suggestions
            $commands = array_values(array_filter($commands, fn ($c) => !in_array($c, $blocked, true)));
            
            foreach ($commands as $cmd) {
                if (str_starts_with($cmd, $lastPart)) {
                    $suggestions[] = $cmd;
                }
            }
        } else {
            // File/directory completion
            $basePath = dirname($lastPart);
            $filename = basename($lastPart);

            if ($basePath === '.') {
                $searchDir = $this->currentWorkingDirectory;
            } elseif (str_starts_with($basePath, '/')) {
                $searchDir = $basePath;
            } else {
                $searchDir = $this->currentWorkingDirectory . '/' . $basePath;
            }

            if (is_dir($searchDir) && is_readable($searchDir)) {
                try {
                    $files = scandir($searchDir);
                    foreach ($files as $file) {
                        if ($file !== '.' && $file !== '..' && str_starts_with($file, $filename)) {
                            $fullPath = $searchDir . '/' . $file;
                            $suggestion = $basePath === '.' ? $file : $basePath . '/' . $file;
                            if (is_dir($fullPath)) {
                                $suggestion .= '/';
                            }
                            $suggestions[] = $suggestion;
                        }
                    }
                } catch (\Throwable $e) {
                    // Ignore file system errors
                }
            }
        }

        return array_slice($suggestions, 0, 10); // Limit suggestions
    }

    private function updateCurrentWorkingDirectory(): void
    {
        try {
            $baseDir = config('terminal.working_directory', base_path());
            $process = new Process(['pwd'], $baseDir);
            $process->run();
            
            if ($process->isSuccessful()) {
                $this->currentWorkingDirectory = trim($process->getOutput());
            } else {
                $this->currentWorkingDirectory = $baseDir;
            }
        } catch (\Throwable $e) {
            $this->currentWorkingDirectory = config('terminal.working_directory', base_path());
        }
    }

    public function getCurrentPath(): string
    {
        if (empty($this->currentWorkingDirectory)) {
            return '~';
        }

        $home = getenv('HOME') ?: '/home/' . get_current_user();
        if (str_starts_with($this->currentWorkingDirectory, $home)) {
            return '~' . substr($this->currentWorkingDirectory, strlen($home));
        }

        return $this->currentWorkingDirectory;
    }

    private function splitBinaryAndArgs(string $command): array
    {
        $parts = preg_split('/\s+/', trim($command));
        $binary = array_shift($parts) ?? '';
        return [$binary, $parts];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return config('terminal.enabled', true);
    }

    public static function canAccess(): bool
    {
        return config('terminal.enabled', true);
    }
}


