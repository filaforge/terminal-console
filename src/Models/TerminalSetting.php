<?php

namespace Filaforge\TerminalConsole\Models;

use Illuminate\Database\Eloquent\Model;

class TerminalSetting extends Model
{
    protected $table = 'terminal_console_settings';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null): ?string
    {
        try {
            $row = static::query()->where('key', $key)->first();
            return $row?->value ?? $default;
        } catch (\Throwable $e) {
            // If the table doesn't exist yet or DB error occurs, gracefully return default
            return $default;
        }
    }

    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
