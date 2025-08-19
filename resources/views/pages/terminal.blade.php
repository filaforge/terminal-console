@php($errors ??= new \Illuminate\Support\ViewErrorBag)
<x-filament::page class="ff-terminal-console">
    <!-- Xterm.js CSS and JS (UMD builds) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@5.3.0/css/xterm.css" />
    <script src="https://cdn.jsdelivr.net/npm/xterm@5.3.0/lib/xterm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.8.0/lib/xterm-addon-fit.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-web-links@0.9.0/lib/xterm-addon-web-links.js"></script>

    <!-- Plugin CSS -->
    @php($cssPath = public_path('vendor/terminal-console/css/index.css'))
    @if(file_exists($cssPath))
        <link rel="stylesheet" href="{{ asset('vendor/terminal-console/css/index.css') }}" />
    @endif

    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap');
    .fi-terminal-container { border-radius: 12px; overflow: hidden; min-height: 60vh; }
        .xterm { font-family: 'JetBrains Mono','Fira Code',monospace !important; font-size: 14px !important; line-height: 1.4 !important; padding: 16px !important; }
    .xterm .xterm-viewport { background: var(--terminal-bg) !important; }
    .xterm .xterm-screen   { background: var(--terminal-bg) !important; }
    /* Preset buttons spacing without relying on Tailwind utilities */
    .fi-preset-btn { margin-right: 10px; }
    </style>



    @php($presets = config('terminal.presets', []))

    <x-filament::section>
        <x-slot name="heading">Terminal Console</x-slot>
        <x-slot name="description">🟢 Connected to ({{ gethostname() }})</x-slot>

        <div class="fi-terminal-container">
            <div id="terminal" wire:ignore x-data="{}"
                 x-init="(() => { const lw = @this; let t=0; const boot=()=>{ if(window.FilaTerminal&&window.FilaTerminal.init){ window.FilaTerminal.init($el,lw);} else if(t++<60){ setTimeout(boot,50);} }; boot(); })()"
                 style="height:60vh;"></div>
        </div>
    </x-filament::section>

    @if(!empty($presets))
    <div x-data="{ active: 'all', view: 'categories' }">
        <x-filament::section>
            @php($__iconMap = [
                // Category-specific icons
                'optimize' => 'heroicon-o-bolt',
                'filament' => 'heroicon-o-sparkles',
                'status' => 'heroicon-o-cpu-chip',
                'files' => 'heroicon-o-folder',
                'github' => 'heroicon-o-code-bracket',
                'php shell' => 'heroicon-o-command-line',
                'nodejs' => 'heroicon-o-globe-alt',
                'docker' => 'heroicon-o-cube',
                'database' => 'heroicon-o-circle-stack',
                // Legacy mappings
                'laravel' => 'heroicon-o-rocket-launch',
                'git' => 'heroicon-o-code-bracket',
                'system' => 'heroicon-o-cpu-chip',
                'file' => 'heroicon-o-folder',
                'folders' => 'heroicon-o-folder',
                'net' => 'heroicon-o-globe-alt',
                'composer' => 'heroicon-o-cube',
                'node' => 'heroicon-o-globe-alt',
                'artisan' => 'heroicon-o-sparkles',
                'data' => 'heroicon-o-circle-stack',
                'php' => 'heroicon-o-command-line',
                'db' => 'heroicon-o-circle-stack',
            ])
            @php($__keys = array_keys($presets))
            @php($__keySlugs = array_map(fn($k) => \Illuminate\Support\Str::slug($k), $__keys))

            <div class="px-2 md:px-4">
                <!-- Categories view -->
                <div class="flex flex-wrap items-center gap-2 mt-2 fi-preset-rows" x-show="view === 'categories'" x-cloak>
                    <!-- Refresh (clear terminal) -->
                    <button type="button"
                        class="fi-btn fi-preset-btn fi-preset-cat fi-terminal-action-btn shadow-sm text-white bg-gray-700 hover:bg-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700 ring-1 ring-gray-600 dark:ring-gray-700"
                        @click="window.FilaTerminal && window.FilaTerminal.refresh && window.FilaTerminal.refresh()"
                        title="Clear terminal"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" aria-hidden="true" />
                    </button>
                    <!-- Toggle to Commands (placed in same row) -->
                    <button type="button"
                        class="fi-btn fi-preset-btn fi-preset-cat fi-terminal-action-btn shadow-sm text-white bg-gray-700 hover:bg-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700 ring-1 ring-gray-600 dark:ring-gray-700"
                        @click="view = 'commands'"
                        title="Show commands"
                    >
                        <x-filament::icon icon="heroicon-o-squares-2x2" class="h-4 w-4" aria-hidden="true" />
                    </button>
                    @foreach(array_keys($presets) as $__grp)
                        @php($__slug = \Illuminate\Support\Str::slug($__grp))
                        @php($__lower = \Illuminate\Support\Str::lower($__grp))
                        @php($__display = $__lower === 'git' ? 'Github' : $__grp)
                        @php($__icon = 'heroicon-o-tag')
                        @foreach($__iconMap as $__k => $__v)
                            @if(str_contains($__lower, $__k))
                                @php($__icon = $__v)
                                @break
                            @endif
                        @endforeach
                        <button type="button"
                            class="fi-btn fi-preset-btn fi-preset-cat fi-terminal-cat-btn shadow-sm text-white bg-gray-700 hover:bg-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700 ring-1 ring-gray-600 dark:ring-gray-700"
                            @click="active = '{{ $__slug }}'; view = 'commands'"
                            :class="active === '{{ $__slug }}' ? 'bg-primary-600 text-white hover:bg-primary-700 dark:hover:bg-primary-500' : ''"
                        >
                            <x-filament::icon :icon="$__icon" class="h-4 w-4" aria-hidden="true" />
                            <span>{{ $__display }}</span>
                        </button>
                    @endforeach
                </div>

                <!-- Commands view -->
                <div class="mt-3 flex flex-wrap gap-2 items-center fi-preset-rows" x-show="view === 'commands'" x-cloak>
                    <!-- Refresh (clear terminal) -->
                    <button type="button"
                        class="fi-btn fi-preset-btn fi-preset-cat fi-terminal-action-btn shadow-sm text-white bg-gray-700 hover:bg-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700 ring-1 ring-gray-600 dark:ring-gray-700"
                        @click="window.FilaTerminal && window.FilaTerminal.refresh && window.FilaTerminal.refresh()"
                        title="Clear terminal"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" aria-hidden="true" />
                    </button>
                    <!-- Toggle back to Categories (same row as commands) -->
                    <button type="button"
                        class="fi-btn fi-preset-btn fi-preset-cat fi-terminal-action-btn shadow-sm text-white bg-gray-700 hover:bg-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700 ring-1 ring-gray-600 dark:ring-gray-700"
                        @click="view = 'categories'"
                        title="Show categories"
                    >
                        <x-filament::icon icon="heroicon-o-list-bullet" class="h-4 w-4" aria-hidden="true" />
                    </button>
                    @foreach($presets as $group => $items)
                        @php($groupSlug = \Illuminate\Support\Str::slug($group))
                        @foreach($items as $item)
                            @php($cmd = (string)($item['command'] ?? ''))
                            <button
                                type="button"
                                title="{{ $item['command'] ?? '' }}"
                                class="fi-btn fi-preset-btn fi-preset-cmd fi-terminal-cmd-btn action-console shadow-sm text-white bg-gray-800 hover:bg-gray-700 dark:bg-gray-900 dark:hover:bg-gray-800 ring-1 ring-gray-700 dark:ring-gray-800"
                                data-group="{{ $groupSlug }}"
                                data-command="{{ $cmd }}"
                                x-show="active !== 'none' && (
                                    active === 'all' ||
                                    active === $el.dataset.group ||
                                    ($el.dataset.group === 'system' && ['files','file','folders'].includes(active)) ||
                                    ($el.dataset.group === 'files' && active === 'net')
                                )"
                                onclick="if(window.FilaTerminal && window.FilaTerminal.insertCommand){window.FilaTerminal.insertCommand(this.dataset.command)}"
                            >
                                <span class="fi-btn-label">{{ \Illuminate\Support\Str::lower($item['label'] ?? ($item['command'] ?? 'command')) }}</span>
                            </button>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </x-filament::section>
    </div>
    @endif

    <script>
    window.FilaTerminal = window.FilaTerminal || (function () {
    let listenersAttached = false;
    let current = null; // { terminal, termEl, fitAddon, state }
    let isRefreshing = false;
    let lastPromptAt = 0;

        function attachGlobalListeners() {
            if (listenersAttached) return; listenersAttached = true;
            const attach = () => {
                if (!window.Livewire) return;
                window.Livewire.on('terminal.output', (payload) => {
                    console.log('Livewire terminal.output event:', payload);
                    if (!current) return; const { terminal, state } = current; const { command, output, path } = payload || {};
                    if (command) terminal.writeln(`$ ${command}`);
                    if (typeof output === 'string' && output.length) {
                        const text = (output ?? '').toString();
                        const normalized = text.replace(/\r\n/g,'\n').replace(/\r/g,'\n').replace(/\n/g,'\r\n');
                        terminal.write(normalized);
                        if (!output.endsWith('\n') && !output.endsWith('\r')) terminal.write('\r\n');
                    }
                    if (path) state.currentPath = path; state.showPrompt();
                });
                window.Livewire.on('terminal.clear', (payload) => {
                    if (!current) return; const { terminal, state } = current; const { path } = payload || {};
                    terminal.clear(); if (path) state.currentPath = path; state.showPrompt();
                });
            };
            if (window.Livewire) attach();
            document.addEventListener('livewire:init', attach, { once: true });
        }

        function init(el, livewireComponent) {
            const termEl = el; if (!termEl || termEl._terminal) return;
            const Terminal = window.Terminal; const FitAddon = window.FitAddon && window.FitAddon.FitAddon; const WebLinksAddon = window.WebLinksAddon && window.WebLinksAddon.WebLinksAddon;
            if (!Terminal) { setTimeout(() => init(termEl, livewireComponent), 50); return; }

            const terminal = new Terminal({
                theme: { background: '#ffffff', foreground: '#1e293b', cursor: '#1e293b', cursorAccent: '#1e293b' },
                fontFamily: '\"JetBrains Mono\", \"Fira Code\", monospace', fontSize: 14, lineHeight: 1.4, cursorBlink: true, cursorStyle: 'block', scrollback: 1000, tabStopWidth: 4
            });
            const fitAddon = FitAddon ? new FitAddon() : null; const webLinksAddon = WebLinksAddon ? new WebLinksAddon() : null;
            if (fitAddon) terminal.loadAddon(fitAddon); if (webLinksAddon) terminal.loadAddon(webLinksAddon);
            terminal.open(termEl); if (fitAddon) fitAddon.fit(); termEl._terminal = terminal; termEl.dataset.initialized = '1';

            let state = {
                currentCommand: '', commandHistory: [], historyIndex: -1,
                currentPath: '{{ $this->getCurrentPath() }}',
                showPrompt: () => {
                    const now = Date.now();
                    if (now - lastPromptAt < 50) return;
                    lastPromptAt = now;
                    // Fixed prompt exactly as requested
                    terminal.write(`\x1b[34madmin@filament\x1b[0m:\x1b[36m~\x1b[0m$ `);
                },
                clearCurrentLine: () => { terminal.write('\x1b[2K\x1b[0G'); },
                refreshPrompt: () => { state.clearCurrentLine(); state.showPrompt(); terminal.write(state.currentCommand); },
            };

            function applyTheme(){
                try {
                    const root = document.querySelector('.ff-terminal-console');
                    if(!root) return;
                    const styles = getComputedStyle(root);
                    const isDark = document.documentElement.classList.contains('dark') || document.documentElement.dataset.theme === 'dark';
                    const bg = (styles.getPropertyValue('--terminal-bg')||'').trim() || (isDark ? '#0a0a0c' : '#ffffff');
                    const fg = (styles.getPropertyValue('--terminal-fg')||'').trim() || (isDark ? '#ffffff' : '#1e293b');
                    const cursor = (styles.getPropertyValue('--terminal-cursor')||'').trim() || fg;
                    terminal.options.theme = { background: bg, foreground: fg, cursor, cursorAccent: cursor };
                    if (current && current.termEl) { current.termEl.style.background = bg; }
                } catch(e) { /* no-op */ }
            }

            const writeWelcome = () => {
                terminal.writeln('\x1b[36mWelcome to Filament Terminal\x1b[0m');
                terminal.writeln('Tab = completion, ↑/↓ = history, Ctrl+L = clear, Ctrl+C = cancel');
                terminal.writeln(''); state.showPrompt();
            };
            const needsWelcomeMessage = () => { try { if (!terminal || !terminal.buffer || !terminal.buffer.active) return true; const lineCount = terminal.buffer.active.length; if (lineCount === 0) return true; for (let i=0; i<Math.min(lineCount,5); i++){ const line = terminal.buffer.active.getLine(i); if (line && line.translateToString().trim()) return false; } return true; } catch(e){ return true; } };
            if (fitAddon) { requestAnimationFrame(() => setTimeout(() => { if (needsWelcomeMessage()) writeWelcome(); }, 0)); } else { if (needsWelcomeMessage()) writeWelcome(); }
            setTimeout(() => terminal.focus(), 150);

            terminal.onKey(({ key, domEvent }) => {
                const printable = !domEvent.altKey && !domEvent.ctrlKey && !domEvent.metaKey;
                if (domEvent.keyCode === 13) {
                    (async () => { const command = state.currentCommand; if (!command.trim()) return; if (state.commandHistory[state.commandHistory.length - 1] !== command) state.commandHistory.push(command); state.historyIndex = -1; terminal.writeln(''); try { if (!livewireComponent) { terminal.writeln('\x1b[31mError: Livewire component not available\x1b[0m'); state.showPrompt(); return; } await livewireComponent.call('$set','data.command',command); await livewireComponent.call('run'); } catch (error) { console.error('Terminal command error:', error); terminal.writeln(`\x1b[31mError: ${error.message}\x1b[0m`); state.showPrompt(); } state.currentCommand = ''; })();
                } else if (domEvent.keyCode === 8) {
                    if (state.currentCommand.length > 0) { state.currentCommand = state.currentCommand.slice(0,-1); terminal.write('\b \b'); }
                } else if (domEvent.keyCode === 9) {
                    domEvent.preventDefault(); (async () => { if (!state.currentCommand.trim()) return; try { if (!livewireComponent) return; const suggestions = await livewireComponent.call('getTabCompletion', state.currentCommand); if (suggestions.length === 1) { const parts = state.currentCommand.split(' '); parts[parts.length - 1] = suggestions[0]; state.currentCommand = parts.join(' '); state.refreshPrompt(); } else if (suggestions.length > 1) { terminal.writeln(''); terminal.writeln(suggestions.join('    ')); state.showPrompt(); terminal.write(state.currentCommand); } } catch (e) { console.error('Tab completion error:', e); } })();
                } else if (domEvent.keyCode === 38) {
                    if (state.commandHistory.length > 0) { if (state.historyIndex === -1) state.historyIndex = state.commandHistory.length - 1; else if (state.historyIndex > 0) state.historyIndex--; state.currentCommand = state.commandHistory[state.historyIndex] || ''; state.refreshPrompt(); }
                } else if (domEvent.keyCode === 40) {
                    if (state.historyIndex >= 0) { if (state.historyIndex < state.commandHistory.length - 1) { state.historyIndex++; state.currentCommand = state.commandHistory[state.historyIndex]; } else { state.historyIndex = -1; state.currentCommand = ''; } state.refreshPrompt(); }
                } else if (domEvent.keyCode === 67 && domEvent.ctrlKey) {
                    terminal.writeln('^C'); state.currentCommand = ''; state.showPrompt();
                } else if (domEvent.keyCode === 76 && domEvent.ctrlKey) {
                    terminal.clear(); state.showPrompt();
                } else if (printable) {
                    state.currentCommand += key; terminal.write(key);
                }
            });

            window.addEventListener('resize', () => { if (fitAddon) fitAddon.fit(); });
            const resizeObserver = new ResizeObserver(() => { if (fitAddon) fitAddon.fit(); }); resizeObserver.observe(termEl);
            const containerEl = document.querySelector('.fi-terminal-container'); if (containerEl) resizeObserver.observe(containerEl);
            const refit = () => { if (fitAddon) { try { fitAddon.fit(); } catch (e) {} } }; if (document.fonts && document.fonts.ready) document.fonts.ready.then(refit);
            termEl.addEventListener('click', () => terminal.focus());
            current = { terminal, termEl, fitAddon, state };
            applyTheme();
            // Watch for dark mode toggles (Filament toggles .dark on <html>)
            try {
                const obs = new MutationObserver(() => { applyTheme(); });
                obs.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
                // Also listen to prefers-color-scheme changes as fallback
                const mq = window.matchMedia('(prefers-color-scheme: dark)');
                if (mq && mq.addEventListener) { mq.addEventListener('change', applyTheme); }
                setTimeout(applyTheme, 50);
                setTimeout(applyTheme, 250);
            } catch(e) {}
            setTimeout(() => { if (needsWelcomeMessage()) writeWelcome(); }, 100);
        }

        attachGlobalListeners();

        function refresh(){
            try {
                if (!current||!current.terminal||!current.state) return;
                if (isRefreshing) return; isRefreshing = true;
                const { terminal, state } = current;
                state.currentCommand = '';
                // Clear screen and scrollback, move cursor to home, and clear current line
                terminal.write('\x1b[2J\x1b[3J\x1b[H');
                terminal.write('\x1b[2K\r');
                state.showPrompt();
                terminal.focus();
                setTimeout(() => { isRefreshing = false; }, 50);
            } catch(e) { isRefreshing = false; }
        }
    async function clearAndRun(command){ try { if (!current||!current.terminal||!current.state) return; await runCommand('clear'); await runCommand(command); } catch(e){} }
    function insertCommand(command){ try { if (!current||!current.terminal||!current.state) return; current.state.currentCommand = String(command ?? ''); current.state.refreshPrompt(); current.terminal.focus(); } catch(e){} }
    async function runCommand(command){ try { if (!current||!current.terminal||!current.state) return; const cmd = String(command ?? '').trim(); if(!cmd) return; current.state.currentCommand = cmd; current.state.refreshPrompt(); const lv = @this; await lv.call('$set','data.command',cmd); await lv.call('run'); } catch(e){} }
    return { init, insertCommand, runCommand, refresh, clearAndRun };
    })();
    </script>
</x-filament::page>
