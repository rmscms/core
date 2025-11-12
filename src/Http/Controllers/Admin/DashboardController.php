<?php

namespace RMS\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use RMS\Core\Controllers\Admin\ProjectAdminController;

class DashboardController extends ProjectAdminController
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
		//$this->useUserTemplates();



		$this->view
			->setTpl('dashboard')
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
		return $this->view(true);
	}
}


