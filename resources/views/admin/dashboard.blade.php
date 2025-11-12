@extends('cms::admin.layout.index')
@section('content')
	<div class="container-fluid">
		<div class="row g-3">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<h5 class="mb-1">{{ $title ?? 'داشبورد' }} ✨</h5>
						<p class="text-muted mb-0">{{ $admin?->name }}</p>
					</div>
				</div>
			</div>

			<div class="col-12 col-lg-6">
				<div class="card">
					<div class="card-header">
						<h6 class="mb-0">ℹ️ اطلاعات سیستم</h6>
					</div>
					<div class="card-body">
						<ul class="list-unstyled mb-0">
							<li class="mb-1"><span class="text-muted">PHP:</span> {{ $meta['php'] ?? '' }}</li>
							<li class="mb-1"><span class="text-muted">Laravel:</span> {{ $meta['laravel'] ?? '' }}</li>
							<li class="mb-1"><span class="text-muted">ENV:</span> {{ $meta['env'] ?? '' }}</li>
							<li class="mb-0"><span class="text-muted">Time:</span> {{ $meta['time'] ?? '' }}</li>
						</ul>
					</div>
				</div>
			</div>

			<div class="col-12 col-lg-6">
				<div class="card">
					<div class="card-header">
						<h6 class="mb-0">📘 راهنمای توسعه</h6>
					</div>
					<div class="card-body">
						<ul class="mb-2">
							<li>ساخت صفحات سفارشی ادمین با الگوی custom_page (تم core)</li>
							<li>افزودن JS/CSS با <code>$this->view->withJs()</code> و <code>withCss()</code></li>
							<li>تزریق متغیرها با <code>withVariables()</code> و <code>withJsVariables()</code></li>
						</ul>
						<p class="text-muted">قابل‌اورراید از طریق <code>config/cms.php</code> و publish ویوها.</p>
						<pre class="mb-0"><code>'dashboard' => [
	'view' => 'cms::admin.dashboard',
	'title' => 'داشبورد من',
]</code></pre>
					</div>
				</div>
			</div>

			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<h6 class="mb-0">🛣️ اورراید روت‌های داشبورد (ساده و تمیز)</h6>
					</div>
					<div class="card-body">
						<ol class="mb-3">
							<li class="mb-2">
								غیرفعال کردن روت پیش‌فرض داشبورد در پکیج (اختیاری):
								<pre class="mt-2 mb-0"><code>// config/cms.php (پروژه)
'dashboard' => [
	'enabled' => false,
	'view' => 'pages.dashboard', // ویوی اختصاصی پروژه
	'title' => 'داشبورد',
],</code></pre>
							</li>
							<li class="mb-2">
								تعریف روت‌های اختصاصی داشبورد در پروژه:
								<pre class="mt-2 mb-0"><code>// routes/web.php (پروژه)
Route::prefix(config('cms.admin_url','admin'))
	->name('admin.')
	->middleware(['web','auth.admin'])
	->group(function () {
		Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
		Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);
});</code></pre>
							</li>
							<li class="mb-2">
								الگوی کنترلر پروژه (custom_page):
								<pre class="mt-2 mb-0"><code>// app/Http/Controllers/Admin/DashboardController.php
class DashboardController extends \RMS\Core\Controllers\Admin\AdminController {
	public function table(): string { return 'admins'; }
	public function modelName(): string { return \RMS\Core\Models\Admin::class; }
	public function index(\Illuminate\Http\Request $request) {
		$this->title('داشبورد');
		$this->useUserTemplates();              // ⬅️ رندر از قالب‌های پروژه
		$this->view->setTpl('pages.dashboard'); // resources/views/admin/pages/dashboard.blade.php
		return $this->view(false);              // ⬅️ بدون namespace پکیج
	}
}</code></pre>
							</li>
							<li class="mb-2">
								تغییر پیشوند ادمین (اختیاری):
								<pre class="mt-2 mb-0"><code>// config/cms.php
'admin_url' => 'admin', // مثلاً 'panel' یا 'dashboard'</code></pre>
							</li>
							<li class="mb-0">
								بهینه‌سازی پروداکشن:
								<pre class="mt-2 mb-0"><code>php artisan config:cache
php artisan route:cache</code></pre>
							</li>
						</ol>
						<p class="text-muted mb-0">توجه: با فعال بودن <code>cms.dashboard.enabled=true</code>، روت‌های داخلی پکیج برای داشبورد ثبت می‌شوند؛ برای روت سفارشی بهتر است آن را <strong>false</strong> کنید و روت‌ها را در پروژه تعریف کنید.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection


