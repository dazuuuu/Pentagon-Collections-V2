<?php
/**
 * Migration runner.
 *   php apps/apps_collections/database/migrate.php            Run all pending migrations
 *   php apps/apps_collections/database/migrate.php --fresh    Drop all known tables, then run every migration
 */

require dirname(__DIR__) . '/app/paths.php';
require APP_ROOT . '/vendor/autoload.php';

use App\Core\Env;
use App\Services\MigrationService;

Env::load();

$fresh = in_array('--fresh', $argv, true);

if ($fresh) {
    echo "Dropping existing tables...\n";
    MigrationService::dropKnownTables();
}

$result = MigrationService::runPending();

foreach ($result['ran'] as $name) {
    echo "Migrating: {$name}\n";
}

if ($result['error']) {
    fwrite(STDERR, 'Error: ' . $result['error'] . "\n");
    exit(1);
}

echo $result['ran'] ? 'Ran ' . count($result['ran']) . " migration(s).\n" : "Nothing to migrate.\n";
