<?php

namespace Filaforge\TerminalConsole\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
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
     * @var list<array{command:string, exit:int|null, output:string}>
     */
    public array $history = [];

        public ?int $exitCode = null;

    public string $currentWorkingDirectory = '';

    public array $commandHistory = [];

    public int $historyIndex = -1;

    public function mount(): void
    {
        $this->updateCurrentWorkingDirectory();
        $this->loadCommandHistory();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('command')
                    ->label('Command')
                    ->placeholder('php artisan migrate --force')
                    ->required()
                    ->extraAlpineAttributes([
                        'x-on:keydown.ctrl.enter.prevent' => '$wire.run()',
                    ]),
                Textarea::make('output')
                    ->rows(1)
                    ->disabled()
                    ->dehydrated(false)
                    ->hidden(),
            ])
            ->statePath('data');
    }

    public function run(): void
    {
        $state = $this->form->getState();
        $full = trim((string) ($state['command'] ?? ''));

        if ($full === '') {
            Notification::make()->title('Command is required')->danger()->send();
            return;
        }

        [$binary, $args] = $this->splitBinaryAndArgs($full);

        // Handle shell built-ins like `cd` without spawning a process
        if ($binary === 'cd') {
            $this->handleCdCommand($args);
            $this->exitCode = 0;

            // Append to history
            $this->history[] = [
                'command' => $full,
                'exit' => $this->exitCode,
                'output' => '',
            ];

            // Add command to history store
            $this->addToCommandHistory($full);

            // Clear input and set last output only
            $this->form->fill([
                'command' => '',
                'output' => '',
            ]);

            // Notify frontend terminal to update prompt
            try {
                $this->dispatch('terminal.output',
                    command: $full,
                    output: '',
                    exit: $this->exitCode,
                    path: $this->getCurrentPath()
                );
            } catch (\Throwable $e) {
                // Ignore dispatch errors
            }

            Notification::make()->title('Directory changed')->success()->send();
            return;
        }

        // Built-in `clear` / `cls` command
        if (in_array($binary, ['clear', 'cls'], true)) {
            $this->exitCode = 0;

            // Track history
            $this->history[] = [
                'command' => $full,
                'exit' => $this->exitCode,
                'output' => '',
            ];
            $this->addToCommandHistory($full);

            // Clear mirrored output
            $this->form->fill([
                'command' => '',
                'output' => '',
            ]);

            // Tell frontend to clear the terminal UI and redraw prompt
            try {
                $this->dispatch('terminal.clear', path: $this->getCurrentPath());
            } catch (\Throwable $e) {
                // ignore
            }

            return;
        }

        // Security: enforce allowlist
        $allowAny = (bool) config('terminal.allow_any', false);
        $allowedBinaries = (array) config('terminal.allowed_binaries', []);
        if (! $allowAny && ! in_array($binary, $allowedBinaries, true)) {
            Notification::make()->title('Binary not allowed')->danger()->send();
            return;
        }

        // Use current working directory for command execution
        $workingDir = !empty($this->currentWorkingDirectory) ? $this->currentWorkingDirectory : (string) config('terminal.working_directory', base_path());
        $process = new Process(array_merge([$binary], $args), $workingDir);
        $process->setTimeout((int) config('terminal.timeout', 60));

        try {
            $process->run();
            $this->exitCode = $process->getExitCode();
            $output = $process->getOutput() . ($process->getErrorOutput() ? "\n" . $process->getErrorOutput() : '');
        } catch (\Throwable $e) {
            $this->exitCode = 1;
            $output = $e->getMessage();
        }

        // Append to history
        $this->history[] = [
            'command' => $full,
            'exit' => $this->exitCode,
            'output' => $output,
        ];

        // Trim history
        $max = (int) config('terminal.max_history', 100);
        if (count($this->history) > $max) {
            $this->history = array_slice($this->history, -$max);
        }

        // Add command to history
        $this->addToCommandHistory($full);

        // Clear input and set last output only (no accumulated history)
        $this->form->fill([
            'command' => '',
            'output' => $output,
        ]);

        // Notify frontend terminal to render the output
        try {
            $this->dispatch('terminal.output',
                command: $full,
                output: $output,
                exit: $this->exitCode,
                path: $this->getCurrentPath()
            );
        } catch (\Throwable $e) {
            // Ignore dispatch errors to avoid breaking UX
        }

        if ($this->exitCode === 0) {
            Notification::make()->title('Command finished')->success()->send();
        } else {
            Notification::make()->title('Command failed')->danger()->send();
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
                    $this->currentWorkingDirectory = realpath($targetDir);
                }
            } elseif (str_starts_with($targetDir, '~/')) {
                // Home relative path
                $home = getenv('HOME') ?: '/home/' . get_current_user();
                $fullPath = $home . '/' . substr($targetDir, 2);
                if (is_dir($fullPath)) {
                    $this->currentWorkingDirectory = realpath($fullPath);
                }
            } else {
                // Relative path
                $fullPath = $this->currentWorkingDirectory . '/' . $targetDir;
                if (is_dir($fullPath)) {
                    $this->currentWorkingDirectory = realpath($fullPath);
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
        $maxHistory = 100;
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
        $this->commandHistory = session('terminal_command_history', []);
    }

    private function saveCommandHistory(): void
    {
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
        $parts = explode(' ', $partialCommand);
        $lastPart = end($parts);

        $suggestions = [];

        // Command completion for first word
        if (count($parts) === 1) {
            $commands = ['cd', 'ls', 'pwd', 'whoami', 'php', 'composer', 'git', 'cat', 'grep', 'find', 'mkdir', 'rmdir', 'cp', 'mv', 'rm'];
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

            if (is_dir($searchDir)) {
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
            }
        }

        return array_slice($suggestions, 0, 10); // Limit suggestions
    }

    private function updateCurrentWorkingDirectory(): void
    {
        try {
            $process = new Process(['pwd'], (string) config('terminal.working_directory', base_path()));
            $process->run();
            if ($process->isSuccessful()) {
                $this->currentWorkingDirectory = trim($process->getOutput());
            } else {
                $this->currentWorkingDirectory = (string) config('terminal.working_directory', base_path());
            }
        } catch (\Throwable $e) {
            $this->currentWorkingDirectory = (string) config('terminal.working_directory', base_path());
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
        $binary = array_shift($parts);
        return [$binary, $parts];
    }

    private function renderHistory(): string
    {
        return collect($this->history)
            ->map(function ($item): string {
                $prompt = sprintf("$ %s\n", $item['command']);
                $out = (string) $item['output'];
                $exit = $item['exit'];
                $exitLine = "\n[exit: " . ($exit === null ? '-' : (string) $exit) . "]\n";
                return $prompt . $out . $exitLine;
            })
            ->implode("\n");
    }
}


