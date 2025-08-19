<?php

namespace Filaforge\TerminalConsole\Commands;

use Illuminate\Console\Command;

class TerminalCommand extends Command
{
    protected $signature = 'terminal-console:install 
                          {--force : Overwrite existing configuration}';

    protected $description = 'Install the Terminal Console plugin';

    public function handle(): int
    {
        $this->info('Installing Terminal Console Plugin...');

        // Publish configuration
        $this->call('vendor:publish', [
            '--tag' => 'terminal-console-config',
            '--force' => $this->option('force'),
        ]);

        // Publish assets
        $this->call('vendor:publish', [
            '--tag' => 'terminal-console-assets',
            '--force' => $this->option('force'),
        ]);

        $this->newLine();
        $this->info('✅ Terminal Console plugin installed successfully!');
        $this->newLine();

        $this->comment('Next steps:');
        $this->line('1. Register the plugin in your Panel Provider:');
        $this->line('   ->plugins([\\Filaforge\\TerminalConsole\\TerminalConsolePlugin::make()])');
        $this->line('2. Configure allowed commands in config/terminal.php');
        $this->line('3. Review security settings for production use');

        return self::SUCCESS;
    }
}
