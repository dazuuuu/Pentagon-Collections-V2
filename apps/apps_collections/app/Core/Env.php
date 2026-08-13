<?php

namespace App\Core;

/**
 * Loads .env (via vlucas/phpdotenv) once per request from APP_ROOT
 * (apps/apps_collections/, outside the web root) and exposes env lookup.
 */
class Env
{
    private static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }
        if (!defined('APP_ROOT')) {
            require dirname(__DIR__) . '/paths.php';
        }
        if (file_exists(APP_ROOT . '/.env')) {
            $dotenv = \Dotenv\Dotenv::createImmutable(APP_ROOT);
            $dotenv->safeLoad();
        }
        self::$loaded = true;
    }

    public static function get(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value === false || $value === null || $value === '') {
            return $default;
        }
        return $value;
    }
}
