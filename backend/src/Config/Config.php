<?php

declare(strict_types=1);

namespace MoodleImportApp\Config;

use RuntimeException;

class Config
{
    /** @var array<string, string> */
    private static array $config = [];

    public static function load(string $envFile): void
    {
        if (!file_exists($envFile)) {
            throw new RuntimeException('Environment file not found: ' . $envFile);
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = trim(rtrim($parts[0]));
            $value = trim(rtrim($parts[1]), '"\'');

            static::$config[$key] = $value;
        }
    }

    public static function get(string $key, string $default = ''): string
    {
        return static::$config[$key] ?? $default;
    }

    public static function getInt(string $key, int $default = 0): int
    {
        $value = static::get($key, (string)$default);
        return (int)$value;
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = static::get($key, $default ? '1' : '0');
        return in_array(strtolower($value), ['1', 'true', 'yes'], true);
    }
}