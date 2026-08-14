<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Url;
use App\Core\View;
use App\Services\MigrationService;

/** Super-admin Settings: run pending PHP migrations after a code update. */
class SettingsController extends BaseAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->requireSuperAdmin('Only super admins can run database migrations.');
    }

    public function index(): void
    {
        View::render('admin.settings.index', [
            'pageTitle' => 'Settings',
            'activeNav' => 'settings',
            'pending' => MigrationService::pending(),
            'applied' => MigrationService::applied(),
        ]);
    }

    public function migrate(): void
    {
        if (!csrfVerify(Request::post('csrf_token'))) {
            flashError('Your session expired. Please try again.');
            redirect($this->safeReturnPath());
        }

        $result = MigrationService::runPending();
        $ran = $result['ran'];

        if ($result['error']) {
            $prefix = $ran
                ? 'Ran ' . count($ran) . ' migration(s), then failed: '
                : 'Migration failed: ';
            flashError($prefix . $result['error']);
        } elseif (!$ran) {
            flashSuccess('Database is already up to date.');
        } else {
            flashSuccess('Ran ' . count($ran) . ' migration(s). The database is up to date.');
        }

        redirect($this->safeReturnPath());
    }

    private function safeReturnPath(): string
    {
        $path = (string) Request::post('redirect_to', '/admin/settings');
        $current = Url::currentPath();
        if ($path === '/admin' || str_starts_with($path, '/admin/')) {
            return $path;
        }
        if ($current === '/admin' || str_starts_with($current, '/admin/')) {
            return $current;
        }
        return '/admin/settings';
    }
}
