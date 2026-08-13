<?php
/**
 * Filesystem roots for this app.
 *
 * Hosting layout (shared hosting):
 *   public_html/                         <- contents of /views (document root)
 *   apps/apps_collections/               <- this file's APP_ROOT (outside the web root)
 *
 * In this repository the same sibling relationship is:
 *   views/                               <- document root
 *   apps/apps_collections/
 */

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

if (!defined('PUBLIC_PATH')) {
    $accountRoot = dirname(APP_ROOT, 2);
    $publicPath = $accountRoot . '/views';
    foreach (['views', 'public_html', 'public'] as $dir) {
        if (is_dir($accountRoot . '/' . $dir)) {
            $publicPath = $accountRoot . '/' . $dir;
            break;
        }
    }
    define('PUBLIC_PATH', $publicPath);
}
