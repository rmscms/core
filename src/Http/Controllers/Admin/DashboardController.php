<?php

namespace RMS\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use RMS\Core\Controllers\Admin\AdminController;

class DashboardController extends AdminController
{
	/**
	 * Required by AdminController (not used for dashboard data).
	 */
	public function table(): string
	{
		return 'admins';
	}

	/**
	 * Required by AdminController (not used for dashboard data).
	 */
	public function modelName(): string
	{
		return \RMS\Core\Models\Admin::class;
	}

	/**
	 * Show the admin dashboard using core theming system (custom_page style).
	 */
	public function index(Request $request)
	{
		$this->title(config('cms.dashboard.title', 'داشبورد'));

		// Use user templates (custom_page pattern) and render from project views
		$this->useUserTemplates();

		// Resolve template from config to a relative user template path (default: pages.dashboard)
		$cfg = (string) config('cms.dashboard.view', 'pages.dashboard');
		$tpl = $cfg;
		if (str_starts_with($cfg, 'cms::admin.')) {
			$tpl = substr($cfg, strlen('cms::admin.'));
		} elseif (str_starts_with($cfg, 'admin.')) {
			$tpl = substr($cfg, strlen('admin.'));
		}

		$this->view
			->setTpl($tpl)
			->withVariables([
				'admin' => $this->getAdmin(),
				'meta' => [
					'php' => PHP_VERSION,
					'laravel' => app()->version(),
					'env' => app()->environment(),
					'time' => now()->format('Y/m/d H:i'),
				],
			]);

		// Render from user namespace (no package prefix)
		return $this->view(false);
	}
}


