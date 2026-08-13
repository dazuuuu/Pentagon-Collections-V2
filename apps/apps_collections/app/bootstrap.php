<?php
/**
 * Application bootstrap — required once by the front controller
 * (public/index.php, which becomes public_html/index.php on hosting).
 * All application code, vendor, and .env live in apps/apps_collections/.
 */

require __DIR__ . '/paths.php';
require APP_ROOT . '/vendor/autoload.php';
require __DIR__ . '/Helpers/functions.php';

App\Core\Env::load();
App\Core\Url::init();

error_reporting(E_ALL);
ini_set('display_errors', App\Core\Env::get('APP_DEBUG', '1') === '1' ? '1' : '0');
