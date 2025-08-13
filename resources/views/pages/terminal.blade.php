@php($errors ??= new \Illuminate\Support\ViewErrorBag)
<x-filament::page>
    <!-- Xterm.js CSS and JS (UMD builds) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@5.3.0/css/xterm.css" />
    <script src="https://cdn.jsdelivr.net/npm/xterm@5.3.0/lib/xterm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.8.0/lib/xterm-addon-fit.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-web-links@0.9.0/lib/xterm-addon-web-links.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap');

                /* Keyframe animations */
        @keyframes terminal-glow {
            0%, 100% { box-shadow: 0 0 8px rgba(81, 162, 255, 0.18), 0 0 16px rgba(81, 162, 255, 0.08); }
            50% { box-shadow: 0 0 12px rgba(81, 162, 255, 0.28), 0 0 24px rgba(81, 162, 255, 0.12); }
        }

        @keyframes cursor-blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }

        @keyframes matrix-rain {
            0% { transform: translateY(-100%); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(100vh); opacity: 0; }
        }

        @keyframes typing {
            from { width: 0; }
            to { width: 100%; }
        }

        @keyframes neon-pulse {
            0%, 100% { text-shadow: 0 0 3px #00ff7f, 0 0 6px #00ff7f; }
            50% { text-shadow: 0 0 4px #00ff7f, 0 0 8px #00ff7f; }
        }

                        /* Terminal Container */
        .fi-terminal-container {
            @apply rounded-xl overflow-hidden min-h-[60vh] relative;
            background: linear-gradient(135deg,
                #0d1117 0%,
                #161b22 25%,
                #21262d 50%,
                #161b22 75%,
                #0d1117 100%);
            border: 1px solid rgba(48, 54, 61, 0.8);
            background-clip: padding-box;
            position: relative;
            animation: terminal-glow 4s ease-in-out infinite;
        }

        .fi-terminal-container::before {
            content: '';
            position: absolute;
            top: -1px;
            left: -1px;
            right: -1px;
            bottom: -1px;
            /* Theme glow color */
            background: linear-gradient(45deg, rgba(81, 162, 255, 0.45), rgba(81, 162, 255, 0.25), rgba(81, 162, 255, 0.18));
            border-radius: 12px;
            z-index: -1;
            animation: terminal-glow 4s ease-in-out infinite;
        }

        /* Xterm.js terminal styling */
        .xterm {
            font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace !important;
            font-size: 14px !important;
            line-height: 1.4 !important;
            padding: 16px !important;
        }

        .xterm .xterm-viewport { background: transparent !important; }
        .xterm .xterm-screen { background: transparent !important; }
        /* Hide native scrollbars inside terminal */
        .xterm .xterm-viewport::-webkit-scrollbar { width: 0 !important; height: 0 !important; }
        .xterm .xterm-viewport { scrollbar-width: none; }

        /* Scanlines effect */
        .fi-terminal-output::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(81, 162, 255, 0.02) 2px,
                rgba(81, 162, 255, 0.02) 4px
            );
            pointer-events: none;
            z-index: 1;
        }

                /* Terminal Header */
        .fi-terminal-header {
            @apply flex items-center justify-between p-4 mb-0;
            background: linear-gradient(90deg, rgba(0, 0, 0, 0.8), rgba(26, 26, 46, 0.8));
            border-bottom: 1px solid rgba(0, 255, 127, 0.3);
            position: relative;
            z-index: 2;
        }

        .fi-terminal-title-bar {
            @apply flex items-center gap-3;
        }

        .fi-terminal-dots {
            @apply flex gap-2;
        }

        .fi-terminal-dot {
            @apply w-3 h-3 rounded-full transition-all duration-300;
            box-shadow: 0 0 10px currentColor;
        }

        .fi-terminal-dot--red {
            background: linear-gradient(45deg, #ff5555, #ff7777);
            color: #ff5555;
        }
        .fi-terminal-dot--yellow {
            background: linear-gradient(45deg, #ffb86c, #ffd700);
            color: #ffb86c;
        }
        .fi-terminal-dot--green {
            background: linear-gradient(45deg, #50fa7b, #00ff7f);
            color: #50fa7b;
        }

        .fi-terminal-dot:hover {
            transform: scale(1.2);
            box-shadow: 0 0 15px currentColor;
        }

        .fi-terminal-prompt {
            @apply text-sm font-mono font-semibold;
            color: #51a2ff;
            text-shadow: 0 0 10px #51a2ff;
            animation: neon-pulse 2s ease-in-out infinite;
        }

        .fi-terminal-info {
            @apply text-xs opacity-70;
            color: #8be9fd;
        }



        /* Matrix rain effect (subtle) */
        .fi-matrix-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            opacity: 0.02;
        }

        .fi-matrix-bg::before {
            content: '01010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101';
            position: absolute;
            top: -100%;
            left: 0;
            width: 100%;
            height: 200%;
            color: #51a2ff;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            line-height: 14px;
            word-break: break-all;
            animation: matrix-rain 30s linear infinite;
        }



                /* Responsive adjustments */
        @media (max-width: 768px) {
            .fi-terminal-output {
                min-height: 50vh;
            }

            .fi-terminal-interactive {
                flex-direction: column;
                gap: 8px;
            }
        }

        /* Custom scrollbar */
        .fi-terminal-content::-webkit-scrollbar {
            width: 8px;
        }

        .fi-terminal-content::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 4px;
        }

        .fi-terminal-content::-webkit-scrollbar-thumb {
            background: linear-gradient(45deg, #50fa7b, #8be9fd);
            border-radius: 4px;
        }

        .fi-terminal-content::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(45deg, #8be9fd, #50fa7b);
        }
    </style>

        <div class="fi-page-content space-y-8">
        <!-- Terminal Section -->
        <x-filament::section>
            <x-slot name="heading">Terminal Console</x-slot>
            <x-slot name="description">🟢 Connected to ({{ gethostname() }})</x-slot>

            <div class="fi-terminal-container">
                <!-- Matrix background effect -->
                <div class="fi-matrix-bg"></div>

                <!-- No internal header; relying on Filament section wrapper only -->

                <!-- Xterm.js terminal container -->
                <div
                    id="terminal"
                    wire:ignore
                    x-data
                    x-init="
                        // Retry boot until the global initializer is ready (handles SPA/nav timing)
                        (() => {
                            let tries = 0;
                            const boot = () => {
                                if (window.FilaTerminal && window.FilaTerminal.init) {
                                    window.FilaTerminal.init($el);
                                } else if (tries++ < 40) { // ~2s total
                                    setTimeout(boot, 50);
                                }
                            };
                            boot();
                        })()
                    "
                    style="height: 60vh;"
                ></div>
            </div>
        </x-filament::section>


    </div>

    <script>
        // Global singleton so we can init reliably on SPA navigations and only attach listeners once
        window.FilaTerminal = window.FilaTerminal || (function () {
            let listenersAttached = false;
            let current = null; // { terminal, termEl, fitAddon, state }

            function attachGlobalListeners() {
                if (listenersAttached) return;
                listenersAttached = true;

                // Livewire may already be initialized; attach immediately if available, otherwise on init
                const attach = () => {
                    if (!window.Livewire) return;

                    window.Livewire.on('terminal.output', (payload) => {
                        if (!current) return;
                        const { terminal, state } = current;
                        const { command, output, path } = payload || {};

                        if (command) {
                            terminal.writeln(`$ ${command}`);
                        }
                        if (typeof output === 'string' && output.length > 0) {
                            const text = (output ?? '').toString();
                            const normalized = text.replace(/\r\n/g, '\n').replace(/\r/g, '\n').replace(/\n/g, '\r\n');
                            terminal.write(normalized);
                            if (!output.endsWith('\n') && !output.endsWith('\r')) {
                                terminal.write('\r\n');
                            }
                        }
                        if (path) state.currentPath = path;
                        state.showPrompt();
                    });

                    window.Livewire.on('terminal.clear', (payload) => {
                        if (!current) return;
                        const { terminal, state } = current;
                        const { path } = payload || {};
                        terminal.clear();
                        if (path) state.currentPath = path;
                        state.showPrompt();
                    });
                };

                if (window.Livewire) attach();
                document.addEventListener('livewire:init', attach, { once: true });
            }

            function init(elOrSelector) {
                const termEl = typeof elOrSelector === 'string' ? document.querySelector(elOrSelector) : elOrSelector;
                if (!termEl) return;
                if (termEl.dataset.initialized === '1' && termEl._terminal) return;

                const Terminal = window.Terminal;
                const FitAddon = window.FitAddon ? window.FitAddon.FitAddon : window.FitAddonAddonFit?.FitAddon;
                const WebLinksAddon = window.WebLinksAddon ? window.WebLinksAddon.WebLinksAddon : window.WebLinksAddonAddon?.WebLinksAddon;
                if (!Terminal) {
                    // Assets not ready yet, retry shortly without marking initialized
                    setTimeout(() => init(termEl), 50);
                    return;
                }

                const terminal = new Terminal({
                    theme: {
                        background: 'transparent',
                        foreground: '#f8f8f2',
                        cursor: '#58a6ff',
                        cursorAccent: '#58a6ff',
                        selectionBackground: '#44475a',
                        black: '#21222c',
                        red: '#ff5555',
                        green: '#50fa7b',
                        yellow: '#f1fa8c',
                        blue: '#bd93f9',
                        magenta: '#ff79c6',
                        cyan: '#8be9fd',
                        white: '#f8f8f2',
                        brightBlack: '#6272a4',
                        brightRed: '#ff6e6e',
                        brightGreen: '#69ff94',
                        brightYellow: '#ffffa5',
                        brightBlue: '#d6acff',
                        brightMagenta: '#ff92df',
                        brightCyan: '#a4ffff',
                        brightWhite: '#ffffff'
                    },
                    fontFamily: '"JetBrains Mono", "Fira Code", "Consolas", monospace',
                    fontSize: 14,
                    lineHeight: 1.4,
                    cursorBlink: true,
                    cursorStyle: 'block',
                    scrollback: 1000,
                    tabStopWidth: 4
                });

                const fitAddon = FitAddon ? new FitAddon() : null;
                const webLinksAddon = WebLinksAddon ? new WebLinksAddon() : null;
                if (fitAddon) terminal.loadAddon(fitAddon);
                if (webLinksAddon) terminal.loadAddon(webLinksAddon);

                terminal.open(termEl);
                if (fitAddon) fitAddon.fit();
                termEl._terminal = terminal;
                termEl.dataset.initialized = '1';

                // Terminal state/functions we keep on the instance
                let state = {
                    currentCommand: '',
                    commandHistory: [],
                    historyIndex: -1,
                    currentPath: '{{ $this->getCurrentPath() }}',
                    showPrompt: () => {
                        terminal.write(`\x1b[34mfilaforge@terminal\x1b[0m:\x1b[36m${state.currentPath}\x1b[0m$ `);
                    },
                    clearCurrentLine: () => {
                        terminal.write('\x1b[2K\x1b[0G');
                    },
                    refreshPrompt: () => {
                        state.clearCurrentLine();
                        state.showPrompt();
                        terminal.write(state.currentCommand);
                    },
                };

                const writeWelcome = () => {
                    terminal.writeln('\x1b[36mWelcome to Filament Terminal\x1b[0m');
                    terminal.writeln('Type commands here. Tab = completion, ↑/↓ = history, Ctrl+L = clear, Ctrl+C = cancel');
                    terminal.writeln('');
                    state.showPrompt();
                };

                const needsWelcomeMessage = () => {
                    try {
                        if (!terminal || !terminal.buffer || !terminal.buffer.active) return true;
                        const lineCount = terminal.buffer.active.length;
                        if (lineCount === 0) return true;
                        for (let i = 0; i < Math.min(lineCount, 5); i++) {
                            const line = terminal.buffer.active.getLine(i);
                            if (line && line.translateToString().trim()) return false;
                        }
                        return true;
                    } catch (e) { return true; }
                };

                // Initial welcome/prompt
                if (fitAddon) {
                    requestAnimationFrame(() => setTimeout(() => {
                        if (needsWelcomeMessage()) writeWelcome();
                    }, 0));
                } else {
                    if (needsWelcomeMessage()) writeWelcome();
                }

                // Focus shortly after mount
                setTimeout(() => terminal.focus(), 150);

                // Input handling
                terminal.onKey(({ key, domEvent }) => {
                    const printable = !domEvent.altKey && !domEvent.ctrlKey && !domEvent.metaKey;
                    if (domEvent.keyCode === 13) { // Enter
                        (async () => {
                            const command = state.currentCommand;
                            if (!command.trim()) return;
                            if (state.commandHistory[state.commandHistory.length - 1] !== command) {
                                state.commandHistory.push(command);
                            }
                            state.historyIndex = -1;
                            terminal.writeln('');
                            try {
                                @this.set('data.command', command);
                                await @this.run();
                            } catch (error) {
                                terminal.writeln(`\x1b[31mError: ${error.message}\x1b[0m`);
                            }
                            state.currentCommand = '';
                        })();
                    } else if (domEvent.keyCode === 8) { // Backspace
                        if (state.currentCommand.length > 0) {
                            state.currentCommand = state.currentCommand.slice(0, -1);
                            terminal.write('\b \b');
                        }
                    } else if (domEvent.keyCode === 9) { // Tab
                        domEvent.preventDefault();
                        (async () => {
                            if (!state.currentCommand.trim()) return;
                            try {
                                const suggestions = await @this.getTabCompletion(state.currentCommand);
                                if (suggestions.length === 1) {
                                    const parts = state.currentCommand.split(' ');
                                    parts[parts.length - 1] = suggestions[0];
                                    state.currentCommand = parts.join(' ');
                                    state.refreshPrompt();
                                } else if (suggestions.length > 1) {
                                    terminal.writeln('');
                                    terminal.writeln(suggestions.join('    '));
                                    state.showPrompt();
                                    terminal.write(state.currentCommand);
                                }
                            } catch (e) { console.error('Tab completion error:', e); }
                        })();
                    } else if (domEvent.keyCode === 38) { // Up
                        if (state.commandHistory.length > 0) {
                            if (state.historyIndex === -1) state.historyIndex = state.commandHistory.length - 1;
                            else if (state.historyIndex > 0) state.historyIndex--;
                            state.currentCommand = state.commandHistory[state.historyIndex] || '';
                            state.refreshPrompt();
                        }
                    } else if (domEvent.keyCode === 40) { // Down
                        if (state.historyIndex >= 0) {
                            if (state.historyIndex < state.commandHistory.length - 1) {
                                state.historyIndex++;
                                state.currentCommand = state.commandHistory[state.historyIndex];
                            } else {
                                state.historyIndex = -1;
                                state.currentCommand = '';
                            }
                            state.refreshPrompt();
                        }
                    } else if (domEvent.keyCode === 67 && domEvent.ctrlKey) { // Ctrl+C
                        terminal.writeln('^C');
                        state.currentCommand = '';
                        state.showPrompt();
                    } else if (domEvent.keyCode === 76 && domEvent.ctrlKey) { // Ctrl+L
                        terminal.clear();
                        state.showPrompt();
                    } else if (printable) {
                        state.currentCommand += key;
                        terminal.write(key);
                    }
                });

                // Resize handling
                window.addEventListener('resize', () => { if (fitAddon) fitAddon.fit(); });
                const resizeObserver = new ResizeObserver(() => { if (fitAddon) fitAddon.fit(); });
                resizeObserver.observe(termEl);
                const containerEl = document.querySelector('.fi-terminal-container');
                if (containerEl) resizeObserver.observe(containerEl);
                const refit = () => { if (fitAddon) { try { fitAddon.fit(); } catch (e) {} } };
                if (document.fonts && document.fonts.ready) document.fonts.ready.then(refit);

                termEl.addEventListener('click', () => terminal.focus());

                // Make this the current active terminal for global event handlers
                current = { terminal, termEl, fitAddon, state };

                // If Livewire is already ready, ensure welcome once more shortly after
                setTimeout(() => {
                    if (needsWelcomeMessage()) writeWelcome();
                }, 100);
            }

            // Attach once
            attachGlobalListeners();

            return { init };
        })();
    </script>
</x-filament::page>
