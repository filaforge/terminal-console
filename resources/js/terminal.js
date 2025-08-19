/**
 * Filaforge Terminal Console - JavaScript Module
 * 
 * This module handles the xterm.js terminal functionality for the Filament panel.
 */

class FilaTerminal {
    constructor() {
        this.current = null;
        this.listenersAttached = false;
    }

    /**
     * Initialize the terminal
     */
    init(elOrSelector, livewireComponent) {
        const termEl = typeof elOrSelector === 'string' 
            ? document.querySelector(elOrSelector) 
            : elOrSelector;

        if (!termEl) return;
        if (termEl.dataset.initialized === '1' && termEl._terminal) return;

        const livewire = livewireComponent;
        const Terminal = window.Terminal;
        const FitAddon = window.FitAddon?.FitAddon || window.FitAddonAddonFit?.FitAddon;
        const WebLinksAddon = window.WebLinksAddon?.WebLinksAddon || window.WebLinksAddonAddon?.WebLinksAddon;

        if (!Terminal) {
            setTimeout(() => this.init(termEl, livewireComponent), 50);
            return;
        }

        const terminal = this.createTerminal();
        const fitAddon = FitAddon ? new FitAddon() : null;
        const webLinksAddon = WebLinksAddon ? new WebLinksAddon() : null;

        this.loadAddons(terminal, fitAddon, webLinksAddon);
        this.openTerminal(terminal, termEl, fitAddon);

        const state = this.createTerminalState(terminal);
        this.setupTerminalHandlers(terminal, state, livewire);
        this.setupResizeHandlers(termEl, fitAddon);

    this.current = { terminal, termEl, fitAddon, state, livewire };
        this.showWelcomeIfNeeded(terminal, state);

        setTimeout(() => terminal.focus(), 150);
    }

    /**
     * Insert a command into the current prompt and focus the terminal
     */
    insertCommand(command) {
        try {
            if (!this.current?.terminal || !this.current?.state) return;
            this.current.state.currentCommand = String(command ?? '');
            this.current.state.refreshPrompt();
            this.current.terminal.focus();
        } catch (e) { /* noop */ }
    }

    /**
     * Insert and execute a command immediately
     */
    async runCommand(command) {
        try {
            if (!this.current?.terminal || !this.current?.state) return;
            const cmd = String(command ?? '').trim();
            if (!cmd) return;
            this.current.state.currentCommand = cmd;
            this.current.state.refreshPrompt();
            const livewire = this.current.livewire;
            if (!livewire) return;
            await livewire.call('$set', 'data.command', cmd);
            await livewire.call('run');
        } catch (e) { /* noop */ }
    }

    /**
     * Create terminal instance with theme
     */
    createTerminal() {
        return new window.Terminal({
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
    }

    /**
     * Load terminal addons
     */
    loadAddons(terminal, fitAddon, webLinksAddon) {
        if (fitAddon) terminal.loadAddon(fitAddon);
        if (webLinksAddon) terminal.loadAddon(webLinksAddon);
    }

    /**
     * Open terminal and fit to container
     */
    openTerminal(terminal, termEl, fitAddon) {
        terminal.open(termEl);
        if (fitAddon) fitAddon.fit();
        termEl._terminal = terminal;
        termEl.dataset.initialized = '1';
    }

    /**
     * Create terminal state object
     */
    createTerminalState(terminal) {
        return {
            currentCommand: '',
            commandHistory: [],
            historyIndex: -1,
            currentPath: '~',
            
            showPrompt() {
                terminal.write(`\x1b[34mfilaforge@terminal\x1b[0m:\x1b[36m${this.currentPath}\x1b[0m$ `);
            },
            
            clearCurrentLine() {
                terminal.write('\x1b[2K\x1b[0G');
            },
            
            refreshPrompt() {
                this.clearCurrentLine();
                this.showPrompt();
                terminal.write(this.currentCommand);
            }
        };
    }

    /**
     * Setup terminal input handlers
     */
    setupTerminalHandlers(terminal, state, livewire) {
        terminal.onKey(({ key, domEvent }) => {
            const printable = !domEvent.altKey && !domEvent.ctrlKey && !domEvent.metaKey;

            switch (domEvent.keyCode) {
                case 13: // Enter
                    this.handleEnterKey(terminal, state, livewire);
                    break;
                case 8: // Backspace
                    this.handleBackspace(terminal, state);
                    break;
                case 9: // Tab
                    domEvent.preventDefault();
                    this.handleTabCompletion(terminal, state, livewire);
                    break;
                case 38: // Up Arrow
                    this.handleArrowUp(terminal, state);
                    break;
                case 40: // Down Arrow
                    this.handleArrowDown(terminal, state);
                    break;
                case 67: // Ctrl+C
                    if (domEvent.ctrlKey) this.handleCtrlC(terminal, state);
                    break;
                case 76: // Ctrl+L
                    if (domEvent.ctrlKey) this.handleCtrlL(terminal, state);
                    break;
                default:
                    if (printable) {
                        state.currentCommand += key;
                        terminal.write(key);
                    }
            }
        });
    }

    /**
     * Handle Enter key press
     */
    async handleEnterKey(terminal, state, livewire) {
        const command = state.currentCommand;
        if (!command.trim()) return;

        if (state.commandHistory[state.commandHistory.length - 1] !== command) {
            state.commandHistory.push(command);
        }
        state.historyIndex = -1;
        terminal.writeln('');

        try {
            if (!livewire) {
                terminal.writeln('\x1b[31mError: Livewire component not available\x1b[0m');
                state.showPrompt();
                return;
            }

            await livewire.call('$set', 'data.command', command);
            await livewire.call('run');
        } catch (error) {
            console.error('Terminal command error:', error);
            terminal.writeln(`\x1b[31mError: ${error.message}\x1b[0m`);
            state.showPrompt();
        }
        
        state.currentCommand = '';
    }

    /**
     * Handle Backspace key
     */
    handleBackspace(terminal, state) {
        if (state.currentCommand.length > 0) {
            state.currentCommand = state.currentCommand.slice(0, -1);
            terminal.write('\b \b');
        }
    }

    /**
     * Handle Tab completion
     */
    async handleTabCompletion(terminal, state, livewire) {
        if (!state.currentCommand.trim()) return;

        try {
            if (!livewire) return;
            const suggestions = await livewire.call('getTabCompletion', state.currentCommand);
            
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
        } catch (e) {
            console.error('Tab completion error:', e);
        }
    }

    /**
     * Handle Up arrow key
     */
    handleArrowUp(terminal, state) {
        if (state.commandHistory.length > 0) {
            if (state.historyIndex === -1) {
                state.historyIndex = state.commandHistory.length - 1;
            } else if (state.historyIndex > 0) {
                state.historyIndex--;
            }
            state.currentCommand = state.commandHistory[state.historyIndex] || '';
            state.refreshPrompt();
        }
    }

    /**
     * Handle Down arrow key
     */
    handleArrowDown(terminal, state) {
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
    }

    /**
     * Handle Ctrl+C
     */
    handleCtrlC(terminal, state) {
        terminal.writeln('^C');
        state.currentCommand = '';
        state.showPrompt();
    }

    /**
     * Handle Ctrl+L
     */
    handleCtrlL(terminal, state) {
        terminal.clear();
        state.showPrompt();
    }

    /**
     * Setup resize handlers
     */
    setupResizeHandlers(termEl, fitAddon) {
        const refit = () => {
            if (fitAddon) {
                try {
                    fitAddon.fit();
                } catch (e) {
                    // Ignore resize errors
                }
            }
        };

        window.addEventListener('resize', refit);
        
        const resizeObserver = new ResizeObserver(refit);
        resizeObserver.observe(termEl);
        
        const containerEl = document.querySelector('.fi-terminal-container');
        if (containerEl) resizeObserver.observe(containerEl);

        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(refit);
        }

        termEl.addEventListener('click', () => {
            if (this.current?.terminal) {
                this.current.terminal.focus();
            }
        });
    }

    /**
     * Show welcome message if needed
     */
    showWelcomeIfNeeded(terminal, state) {
        const needsWelcome = () => {
            try {
                if (!terminal?.buffer?.active) return true;
                const lineCount = terminal.buffer.active.length;
                if (lineCount === 0) return true;
                
                for (let i = 0; i < Math.min(lineCount, 5); i++) {
                    const line = terminal.buffer.active.getLine(i);
                    if (line && line.translateToString().trim()) return false;
                }
                return true;
            } catch (e) {
                return true;
            }
        };

        const writeWelcome = () => {
            terminal.writeln('\x1b[36mWelcome to Filament Terminal Console\x1b[0m');
            terminal.writeln('Type commands here. Tab = completion, ↑/↓ = history, Ctrl+L = clear, Ctrl+C = cancel');
            terminal.writeln('');
            state.showPrompt();
        };

        if (needsWelcome()) {
            setTimeout(() => {
                if (needsWelcome()) writeWelcome();
            }, 100);
        }
    }

    /**
     * Attach global Livewire listeners
     */
    attachGlobalListeners() {
        if (this.listenersAttached) return;
        this.listenersAttached = true;

        const attach = () => {
            if (!window.Livewire) return;

            window.Livewire.on('terminal.output', (payload) => {
                if (!this.current) return;
                const { terminal, state } = this.current;
                const { command, output, path } = payload || {};

                if (command) {
                    terminal.writeln(`$ ${command}`);
                }
                
                if (typeof output === 'string' && output.length > 0) {
                    const normalized = output.replace(/\r\n/g, '\n').replace(/\r/g, '\n').replace(/\n/g, '\r\n');
                    terminal.write(normalized);
                    if (!output.endsWith('\n') && !output.endsWith('\r')) {
                        terminal.write('\r\n');
                    }
                }
                
                if (path) state.currentPath = path;
                state.showPrompt();
            });

            window.Livewire.on('terminal.clear', (payload) => {
                if (!this.current) return;
                const { terminal, state } = this.current;
                const { path } = payload || {};
                
                terminal.clear();
                if (path) state.currentPath = path;
                state.showPrompt();
            });
        };

        if (window.Livewire) {
            attach();
        } else {
            document.addEventListener('livewire:init', attach, { once: true });
        }
    }
}

// Initialize global singleton
window.FilaTerminal = window.FilaTerminal || new FilaTerminal();
window.FilaTerminal.attachGlobalListeners();
