<?php

declare(strict_types=1);

final class AppEnvironment
{
    public static function load(string $file): void
    {
        if (!is_file($file) || !is_readable($file)) {
            return;
        }
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!preg_match('/^([A-Za-z_][A-Za-z0-9_]*)\\s*=\\s*(.*)$/', $line, $match)) {
                throw new RuntimeException('Ligne .env invalide (clé=valeur attendue).');
            }
            $key = $match[1];
            $value = trim($match[2]);
            if (strlen($value) >= 2 && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))) {
                $value = substr($value, 1, -1);
            } else {
                $value = preg_replace('/\\s+#.*$/', '', $value);
            }
            if (getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
            }
        }
    }
}
