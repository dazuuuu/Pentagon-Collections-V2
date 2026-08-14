<?php

namespace App\Services;

use App\Core\Database;
use PDO;
use Throwable;

/**
 * Shared runner for PHP migration files in database/migrations/.
 * Used by the CLI script and the admin Settings page.
 */
class MigrationService
{
    private const KNOWN_TABLES = [
        'testimonials',
        'countries',
        'application_notes',
        'admin_otp_codes',
        'otp_codes',
        'applications',
        'applicants',
        'admins',
        'migrations',
    ];

    public static function directory(): string
    {
        if (!defined('APP_ROOT')) {
            require dirname(__DIR__) . '/paths.php';
        }
        return APP_ROOT . '/database/migrations';
    }

    public static function ensureTable(?PDO $pdo = null): void
    {
        $pdo = $pdo ?? Database::connection();
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    /** @return string[] Absolute paths, sorted. */
    public static function files(): array
    {
        $files = glob(self::directory() . '/*.php') ?: [];
        sort($files);
        return $files;
    }

    /** @return string[] Migration basenames already recorded. */
    public static function appliedNames(): array
    {
        self::ensureTable();
        return Database::connection()
            ->query('SELECT migration FROM migrations')
            ->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    /**
     * @return array<int, array{name: string, file: string}>
     */
    public static function pending(): array
    {
        $applied = self::appliedNames();
        $pending = [];
        foreach (self::files() as $file) {
            $name = basename($file, '.php');
            if (!in_array($name, $applied, true)) {
                $pending[] = ['name' => $name, 'file' => $file];
            }
        }
        return $pending;
    }

    public static function pendingCount(): int
    {
        return count(self::pending());
    }

    /**
     * @return array<int, array{migration: string, applied_at: string}>
     */
    public static function applied(): array
    {
        self::ensureTable();
        return Database::connection()
            ->query('SELECT migration, applied_at FROM migrations ORDER BY id ASC')
            ->fetchAll() ?: [];
    }

    /**
     * Run every pending migration in order.
     *
     * @return array{ran: string[], error: ?string}
     */
    public static function runPending(): array
    {
        $pdo = Database::connection();
        self::ensureTable($pdo);
        $ran = [];

        foreach (self::pending() as $item) {
            try {
                $migration = require $item['file'];
                if (!is_array($migration) || empty($migration['up']) || !is_string($migration['up'])) {
                    return ['ran' => $ran, 'error' => $item['name'] . ' is not a valid migration file.'];
                }
                $pdo->exec($migration['up']);
                $pdo->prepare('INSERT INTO migrations (migration) VALUES (?)')->execute([$item['name']]);
                $ran[] = $item['name'];
            } catch (Throwable $e) {
                return ['ran' => $ran, 'error' => $item['name'] . ': ' . $e->getMessage()];
            }
        }

        return ['ran' => $ran, 'error' => null];
    }

    /** CLI --fresh only. Drops known tables so every migration can run again. */
    public static function dropKnownTables(): void
    {
        $pdo = Database::connection();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach (self::KNOWN_TABLES as $table) {
            $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
}
