<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Terminal Console Configuration
    |--------------------------------------------------------------------------
    */

    // Enable/disable the terminal console
    'enabled' => env('TERMINAL_CONSOLE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */

    // If true, any command can run EXCEPT those explicitly blocked below.
    // Keep this disabled in production and rely on allowlist when possible.
    'allow_any' => env('TERMINAL_ALLOW_ANY', false),

    // Allowed binaries when 'allow_any' is false. Keep this restrictive in production.
    'allowed_binaries' => [
    // Basic commands
    'ls', 'pwd', 'cd', 'whoami', 'echo', 'cat', 'head', 'tail', 'grep', 'find', 'clear',
        
        // File operations (be careful with these in production)
        'mkdir', 'rmdir', 'cp', 'mv', 'rm', 'touch',
        
        // System info
        'df', 'du', 'ps', 'uname', 'tree', 'which', 'whereis', 'uptime', 'free', 'who', 'ip', 'wc',
        
    // Development tools
    'php', 'composer', '/home/lenovo/bin/composer', 'npm', 'yarn', 'git', 'node', 'filament',
    // Containers & databases
    'docker', 'docker-compose', 'mysql', 'psql', 'redis-cli', 'pnpm',
        
        // Laravel Artisan
        'artisan',
    ],

    // Blocked binaries (always denied). Takes precedence over allow_any & allowed_binaries.
    'blocked_binaries' => [
        // Dangerous/destructive or privilege-escalation prone commands
        'rm', 'mkfs', 'dd', 'shutdown', 'reboot', 'poweroff', 'halt', 'killall',
        'userdel', 'iptables', 'ufw', 'pkexec', 'sudo', 'passwd', 'chpasswd',
    ],

    // Rate limiting (commands per minute per user)
    'rate_limit' => env('TERMINAL_RATE_LIMIT', 60),

    /*
    |--------------------------------------------------------------------------
    | Command Presets
    |--------------------------------------------------------------------------
    */

    'presets' => [
    'Optimize' => [
            [
                'label' => 'clear cache',
                'command' => 'php artisan cache:clear',
                'description' => 'php artisan cache:clear',
                'icon' => 'heroicon-o-trash',
                'color' => 'warning',
                'class' => 'action-console',
            ],
            [
                'label' => 'clear config',
                'command' => 'php artisan config:clear',
                'description' => 'php artisan config:clear',
                'icon' => 'heroicon-o-cog-6-tooth',
                'color' => 'info',
                'class' => 'action-console',
            ],
            [
                'label' => 'clear routes',
                'command' => 'php artisan route:clear',
                'description' => 'php artisan route:clear',
                'icon' => 'heroicon-o-map',
                'color' => 'primary',
                'class' => 'action-console',
            ],
            [
                'label' => 'clear views',
                'command' => 'php artisan view:clear',
                'description' => 'php artisan view:clear',
                'icon' => 'heroicon-o-eye',
                'color' => 'success',
                'class' => 'action-console',
            ],
            [
                'label' => 'clear bootstrap',
                'command' => 'php artisan optimize:clear',
                'description' => 'php artisan optimize:clear',
                'icon' => 'heroicon-o-x-mark',
                'color' => 'gray',
                'class' => 'action-console',
            ],
            [
                'label' => 'flush queue',
                'command' => 'php artisan queue:flush',
                'description' => 'php artisan queue:flush',
                'icon' => 'heroicon-o-sparkles',
                'color' => 'primary',
                'class' => 'action-console',
            ],
            [
                'label' => 'dump autoload',
                'command' => 'composer dump-autoload -o',
                'description' => 'composer dump-autoload -o',
                'icon' => 'heroicon-o-cube',
                'color' => 'warning',
                'class' => 'action-console',
            ],
            [
                'label' => 'optimize app',
                'command' => 'php artisan optimize',
                'description' => 'php artisan optimize',
                'icon' => 'heroicon-o-bolt',
                'color' => 'success',
                'class' => 'action-console',
            ],
        ],
            'Filament' => [
            [
                'label' => 'Make User',
                'command' => 'php artisan make:filament-user',
                'description' => 'Create a new Filament user',
                'icon' => 'heroicon-o-user-plus',
                'color' => 'success',
            ],
            [
                'label' => 'Install',
                'command' => 'php artisan filament:install',
                'description' => 'Install Filament',
                'icon' => 'heroicon-o-arrow-down-tray',
                'color' => 'primary',
            ],
            [
                'label' => 'Upgrade',
                'command' => 'php artisan filament:upgrade',
                'description' => 'Upgrade Filament assets',
                'icon' => 'heroicon-o-arrow-up',
                'color' => 'info',
            ],
            [
                'label' => 'Optimize',
                'command' => 'php artisan filament:optimize',
                'description' => 'Optimize Filament assets',
                'icon' => 'heroicon-o-sparkles',
                'color' => 'warning',
            ],
            [
                'label' => 'Make Resource',
                'command' => 'php artisan make:filament-resource',
                'description' => 'Create a new Filament resource',
                'icon' => 'heroicon-o-document-plus',
                'color' => 'success',
            ],
            [
                'label' => 'Make Page',
                'command' => 'php artisan make:filament-page',
                'description' => 'Create a new Filament page',
                'icon' => 'heroicon-o-document',
                'color' => 'primary',
            ],
            [
                'label' => 'Make Widget',
                'command' => 'php artisan make:filament-widget',
                'description' => 'Create a new Filament widget',
                'icon' => 'heroicon-o-squares-2x2',
                'color' => 'info',
            ],
            [
                'label' => 'Make Theme',
                'command' => 'php artisan make:filament-theme',
                'description' => 'Create a new Filament theme',
                'icon' => 'heroicon-o-paint-brush',
                'color' => 'gray',
            ],
        ],
        
       'Composer' => [
            [
                'label' => 'Disk Usage',
                'command' => 'df -h',
                'description' => 'Show disk space usage',
                'icon' => 'heroicon-o-chart-pie',
                'color' => 'info',
            ],
            [
                'label' => 'Memory Usage',
                'command' => 'ps aux | sort -nr -k 4 | head -10',
                'description' => 'Show top memory consumers',
                'icon' => 'heroicon-o-cpu-chip',
                'color' => 'warning',
            ],
            [
                'label' => 'List Processes',
                'command' => 'ps aux',
                'description' => 'List all running processes',
                'icon' => 'heroicon-o-list-bullet',
                'color' => 'gray',
            ],
            [
                'label' => 'System Info',
                'command' => 'uname -a',
                'description' => 'Show system information',
                'icon' => 'heroicon-o-information-circle',
                'color' => 'info',
            ],
            [
                'label' => 'Uptime',
                'command' => 'uptime',
                'description' => 'Show system uptime and load',
                'icon' => 'heroicon-o-clock',
                'color' => 'success',
            ],
            [
                'label' => 'Free Memory',
                'command' => 'free -h',
                'description' => 'Show memory usage',
                'icon' => 'heroicon-o-cpu-chip',
                'color' => 'warning',
            ],
            [
                'label' => 'CPU Info',
                'command' => 'cat /proc/cpuinfo | grep "model name" | head -1',
                'description' => 'Show CPU information',
                'icon' => 'heroicon-o-cog-6-tooth',
                'color' => 'gray',
            ],
            [
                'label' => 'Who',
                'command' => 'who',
                'description' => 'Show logged in users',
                'icon' => 'heroicon-o-users',
                'color' => 'info',
            ],
        ],

        'System' => [
            [
                'label' => 'Disk Usage',
                'command' => 'df -h',
                'description' => 'Show disk space usage',
                'icon' => 'heroicon-o-chart-pie',
                'color' => 'info',
            ],
            [
                'label' => 'Memory Usage',
                'command' => 'ps aux | sort -nr -k 4 | head -10',
                'description' => 'Show top memory consumers',
                'icon' => 'heroicon-o-cpu-chip',
                'color' => 'warning',
            ],
            [
                'label' => 'List Processes',
                'command' => 'ps aux',
                'description' => 'List all running processes',
                'icon' => 'heroicon-o-list-bullet',
                'color' => 'gray',
            ],
            [
                'label' => 'System Info',
                'command' => 'uname -a',
                'description' => 'Show system information',
                'icon' => 'heroicon-o-information-circle',
                'color' => 'info',
            ],
            [
                'label' => 'Uptime',
                'command' => 'uptime',
                'description' => 'Show system uptime and load',
                'icon' => 'heroicon-o-clock',
                'color' => 'success',
            ],
            [
                'label' => 'Free Memory',
                'command' => 'free -h',
                'description' => 'Show memory usage',
                'icon' => 'heroicon-o-cpu-chip',
                'color' => 'warning',
            ],
            [
                'label' => 'CPU Info',
                'command' => 'cat /proc/cpuinfo | grep "model name" | head -1',
                'description' => 'Show CPU information',
                'icon' => 'heroicon-o-cog-6-tooth',
                'color' => 'gray',
            ],
            [
                'label' => 'Who',
                'command' => 'who',
                'description' => 'Show logged in users',
                'icon' => 'heroicon-o-users',
                'color' => 'info',
            ],
        ],

        'GitHub' => [
            [
                'label' => 'Git Status',
                'command' => 'git status',
                'description' => 'Show git repository status',
                'icon' => 'heroicon-o-information-circle',
                'color' => 'primary',
                'auto_run' => true,
            ],
            [
                'label' => 'Git Log',
                'command' => 'git log --oneline -10',
                'description' => 'Show recent commits',
                'icon' => 'heroicon-o-clock',
                'color' => 'gray',
            ],
            [
                'label' => 'Git Pull',
                'command' => 'git pull origin main',
                'description' => 'Pull latest changes',
                'icon' => 'heroicon-o-arrow-down-tray',
                'color' => 'success',
            ],
            [
                'label' => 'Git Add All',
                'command' => 'git add .',
                'description' => 'Add all changes to staging',
                'icon' => 'heroicon-o-plus',
                'color' => 'info',
            ],
            [
                'label' => 'Git Commit',
                'command' => 'git commit -m "Update: $(date)"',
                'description' => 'Commit staged changes with timestamp',
                'icon' => 'heroicon-o-check',
                'color' => 'success',
            ],
            [
                'label' => 'Git Push',
                'command' => 'git push origin main',
                'description' => 'Push commits to remote',
                'icon' => 'heroicon-o-arrow-up-tray',
                'color' => 'warning',
            ],
            [
                'label' => 'Git Branches',
                'command' => 'git branch -a',
                'description' => 'List all branches',
                'icon' => 'heroicon-o-squares-plus',
                'color' => 'primary',
            ],
            [
                'label' => 'Git Diff',
                'command' => 'git diff',
                'description' => 'Show unstaged changes',
                'icon' => 'heroicon-o-document-text',
                'color' => 'gray',
            ],
            [
                'label' => 'Git Stash',
                'command' => 'git stash',
                'description' => 'Stash current changes',
                'icon' => 'heroicon-o-archive-box',
                'color' => 'info',
            ],
            [
                'label' => 'Git Stash Pop',
                'command' => 'git stash pop',
                'description' => 'Apply and remove latest stash',
                'icon' => 'heroicon-o-archive-box-arrow-down',
                'color' => 'success',
            ],
            [
                'label' => 'Git Remote',
                'command' => 'git remote -v',
                'description' => 'Show remote repositories',
                'icon' => 'heroicon-o-globe-alt',
                'color' => 'primary',
            ],
            [
                'label' => 'Git Clean',
                'command' => 'git clean -fd',
                'description' => 'Remove untracked files and directories',
                'icon' => 'heroicon-o-trash',
                'color' => 'danger',
            ],
        ],

        'Storage' => [
            [
                'label' => 'List Files',
                'command' => 'ls -la',
                'description' => 'List all files with details',
                'icon' => 'heroicon-o-folder',
                'color' => 'primary',
            ],
            [
                'label' => 'Tree View',
                'command' => 'tree -L 2',
                'description' => 'Show directory tree (2 levels)',
                'icon' => 'heroicon-o-folder-open',
                'color' => 'success',
            ],
            [
                'label' => 'Find Large Files',
                'command' => 'find . -type f -size +10M -exec ls -lh {} \\;',
                'description' => 'Find files larger than 10MB',
                'icon' => 'heroicon-o-magnifying-glass',
                'color' => 'warning',
            ],
            [
                'label' => 'Current Directory',
                'command' => 'pwd',
                'description' => 'Show current directory path',
                'icon' => 'heroicon-o-map-pin',
                'color' => 'gray',
            ],
            [
                'label' => 'File Count',
                'command' => 'find . -type f | wc -l',
                'description' => 'Count files in current directory',
                'icon' => 'heroicon-o-calculator',
                'color' => 'info',
            ],
            [
                'label' => 'Directory Size',
                'command' => 'du -sh .',
                'description' => 'Show current directory size',
                'icon' => 'heroicon-o-chart-bar',
                'color' => 'warning',
            ],
            [
                'label' => 'Recently Modified',
                'command' => 'find . -type f -mtime -1 -exec ls -lh {} \\;',
                'description' => 'Files modified in last 24 hours',
                'icon' => 'heroicon-o-clock',
                'color' => 'success',
            ],
        ],

        'PHP Script' => [
            [
                'label' => 'PHP Version',
                'command' => 'php -v',
                'description' => 'Show PHP version',
                'icon' => 'heroicon-o-command-line',
                'color' => 'gray',
                'auto_run' => true,
            ],
            [
                'label' => 'PHP Info',
                'command' => 'php -i | head -20',
                'description' => 'Show PHP configuration info',
                'icon' => 'heroicon-o-information-circle',
                'color' => 'info',
            ],
            [
                'label' => 'PHP Extensions',
                'command' => 'php -m',
                'description' => 'List loaded PHP extensions',
                'icon' => 'heroicon-o-puzzle-piece',
                'color' => 'primary',
            ],
            [
                'label' => 'PHP Syntax Check',
                'command' => 'php -l index.php',
                'description' => 'Check PHP syntax of index.php',
                'icon' => 'heroicon-o-check-circle',
                'color' => 'success',
            ],
            [
                'label' => 'PHP Memory Limit',
                'command' => 'php -r "echo ini_get(\'memory_limit\');"',
                'description' => 'Show PHP memory limit',
                'icon' => 'heroicon-o-cpu-chip',
                'color' => 'info',
            ],
            [
                'label' => 'PHP Error Log',
                'command' => 'php -r "echo ini_get(\'error_log\');"',
                'description' => 'Show PHP error log location',
                'icon' => 'heroicon-o-exclamation-triangle',
                'color' => 'warning',
            ],
            [
                'label' => 'Composer Install',
                'command' => 'composer install',
                'description' => 'Install PHP dependencies',
                'icon' => 'heroicon-o-cube',
                'color' => 'primary',
            ],
            [
                'label' => 'Composer Update',
                'command' => 'composer update',
                'description' => 'Update PHP dependencies',
                'icon' => 'heroicon-o-arrow-path',
                'color' => 'warning',
            ],
            [
                'label' => 'Composer Show',
                'command' => 'composer show',
                'description' => 'Show installed packages',
                'icon' => 'heroicon-o-list-bullet',
                'color' => 'gray',
            ],
            [
                'label' => 'Composer Outdated',
                'command' => 'composer outdated',
                'description' => 'Show outdated packages',
                'icon' => 'heroicon-o-clock',
                'color' => 'warning',
            ],
            [
                'label' => 'Composer Validate',
                'command' => 'composer validate',
                'description' => 'Validate composer.json',
                'icon' => 'heroicon-o-shield-check',
                'color' => 'success',
            ],
            [
                'label' => 'PHP Interactive',
                'command' => 'php -a',
                'description' => 'Start PHP interactive shell',
                'icon' => 'heroicon-o-terminal',
                'color' => 'primary',
            ],
            [
                'label' => 'PHP Built-in Server',
                'command' => 'php -S localhost:8000',
                'description' => 'Start PHP built-in server on port 8000',
                'icon' => 'heroicon-o-server',
                'color' => 'info',
            ],
        ],
        'Node JS' => [
            [
                'label' => 'Node Version',
                'command' => 'node -v',
                'description' => 'Show Node.js version',
                'icon' => 'heroicon-o-bolt',
                'color' => 'gray',
                'auto_run' => true,
            ],
            [
                'label' => 'NPM Version',
                'command' => 'npm -v',
                'description' => 'Show npm version',
                'icon' => 'heroicon-o-information-circle',
                'color' => 'gray',
            ],
            [
                'label' => 'Install (npm)',
                'command' => 'npm install',
                'description' => 'Install JS dependencies (npm)',
                'icon' => 'heroicon-o-arrow-down-tray',
                'color' => 'primary',
            ],
            [
                'label' => 'Build (npm)',
                'command' => 'npm run build',
                'description' => 'Build assets (npm)',
                'icon' => 'heroicon-o-cog-6-tooth',
                'color' => 'success',
            ],
            [
                'label' => 'Dev (npm)',
                'command' => 'npm run dev',
                'description' => 'Run dev server (npm)',
                'icon' => 'heroicon-o-play',
                'color' => 'info',
            ],
            [
                'label' => 'Install (yarn)',
                'command' => 'yarn install',
                'description' => 'Install JS dependencies (yarn)',
                'icon' => 'heroicon-o-arrow-down-tray',
                'color' => 'primary',
            ],
            [
                'label' => 'Install (pnpm)',
                'command' => 'pnpm install',
                'description' => 'Install JS dependencies (pnpm)',
                'icon' => 'heroicon-o-arrow-down-tray',
                'color' => 'primary',
            ],
        ],
        'Docker' => [
            [
                'label' => 'Docker Version',
                'command' => 'docker --version',
                'description' => 'Show Docker version',
                'icon' => 'heroicon-o-information-circle',
                'color' => 'gray',
                'auto_run' => true,
            ],
            [
                'label' => 'Docker Info',
                'command' => 'docker info',
                'description' => 'Display system-wide information',
                'icon' => 'heroicon-o-cpu-chip',
                'color' => 'gray',
            ],
            [
                'label' => 'Containers (ps -a)',
                'command' => 'docker ps -a',
                'description' => 'List containers',
                'icon' => 'heroicon-o-list-bullet',
                'color' => 'primary',
            ],
            [
                'label' => 'Images',
                'command' => 'docker images',
                'description' => 'List images',
                'icon' => 'heroicon-o-photo',
                'color' => 'primary',
            ],
            [
                'label' => 'Compose Up',
                'command' => 'docker compose up -d',
                'description' => 'Start services in detached mode',
                'icon' => 'heroicon-o-play',
                'color' => 'success',
            ],
            [
                'label' => 'Compose Down',
                'command' => 'docker compose down',
                'description' => 'Stop and remove services',
                'icon' => 'heroicon-o-stop',
                'color' => 'danger',
            ],
        ],
        'Database' => [
            [
                'label' => 'MySQL Version',
                'command' => 'mysql --version',
                'description' => 'Show MySQL client version',
                'icon' => 'heroicon-o-circle-stack',
                'color' => 'gray',
                'auto_run' => true,
            ],
            [
                'label' => 'MySQL Databases',
                'command' => "mysql -e 'SHOW DATABASES;'",
                'description' => 'List databases (may require auth)',
                'icon' => 'heroicon-o-list-bullet',
                'color' => 'primary',
            ],
            [
                'label' => 'PostgreSQL Version',
                'command' => 'psql --version',
                'description' => 'Show psql client version',
                'icon' => 'heroicon-o-circle-stack',
                'color' => 'gray',
            ],
            [
                'label' => 'Redis Ping',
                'command' => 'redis-cli ping',
                'description' => 'Ping Redis server',
                'icon' => 'heroicon-o-bolt',
                'color' => 'success',
            ],
        ],
        // 'Files' group removed; its items were merged into 'System'.
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Settings
    |--------------------------------------------------------------------------
    */

    // Working directory for command execution
    'working_directory' => base_path(),

    // Command timeout in seconds
    'timeout' => env('TERMINAL_TIMEOUT', 60),

    // Keep only the last N history entries in memory
    'max_history' => env('TERMINAL_MAX_HISTORY', 100),

    /*
    |--------------------------------------------------------------------------
    | UI Settings
    |--------------------------------------------------------------------------
    */

    // Theme settings
    'theme' => [
        'font_family' => env('TERMINAL_FONT_FAMILY', 'JetBrains Mono'),
        'font_size' => env('TERMINAL_FONT_SIZE', 14),
        'background' => env('TERMINAL_BACKGROUND', 'transparent'),
        'foreground' => env('TERMINAL_FOREGROUND', '#f8f8f2'),
        'cursor' => env('TERMINAL_CURSOR', '#58a6ff'),
    ],

    // Show welcome message on terminal load
    'show_welcome' => env('TERMINAL_SHOW_WELCOME', true),

    // Custom welcome message
    'welcome_message' => env('TERMINAL_WELCOME_MESSAGE', null),

    /*
    |--------------------------------------------------------------------------
    | Logging & Monitoring
    |--------------------------------------------------------------------------
    */

    'logging' => [
        // Enable command execution logging
        'enabled' => env('TERMINAL_LOGGING_ENABLED', true),

        // Log channel (null = default)
        'channel' => env('TERMINAL_LOG_CHANNEL'),

        // Log successful commands
        'log_successful' => env('TERMINAL_LOG_SUCCESSFUL', true),

        // Log failed commands
        'log_failed' => env('TERMINAL_LOG_FAILED', true),

        // Include command output in logs
        'include_output' => env('TERMINAL_LOG_INCLUDE_OUTPUT', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Advanced Settings
    |--------------------------------------------------------------------------
    */

    // Environment variables to set for commands
    'environment_variables' => [
        // You can hard-override PATH here, but prefer TERMINAL_EXTRA_PATH to append instead.
        // 'PATH' => '/usr/local/bin:/usr/bin:/bin',
        // 'NODE_ENV' => 'production',
        // Tip: Set TERMINAL_EXTRA_PATH in .env to append to the current PATH for commands like docker/node/mysql
        // Example: TERMINAL_EXTRA_PATH="/usr/local/sbin:/usr/local/bin:/usr/bin:/bin:/snap/bin"
        'HOME' => '/home/lenovo',
        'COMPOSER_HOME' => '/home/lenovo/.composer',
    ],

    // Enable tab completion
    'tab_completion' => env('TERMINAL_TAB_COMPLETION', true),

    // Enable command history
    'command_history' => env('TERMINAL_COMMAND_HISTORY', true),

    // Enable keyboard shortcuts
    'keyboard_shortcuts' => env('TERMINAL_KEYBOARD_SHORTCUTS', true),
];


