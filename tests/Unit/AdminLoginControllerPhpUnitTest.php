<?php

namespace RMS\Core\Tests\Unit;

use Tests\TestCase;
use RMS\Core\Controllers\Auth\AdminLoginController;
use RMS\Core\Models\Admin;
use RMS\Core\Services\AdminAuthService;
use RMS\Core\Services\AdminRateLimiter;
use RMS\Core\Http\Requests\AdminLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Validation\ValidationException;
use Mockery;

class AdminLoginControllerPhpUnitTest extends TestCase
{
    protected AdminLoginController $controller;
    protected Admin $testAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configure auth for admin guard
        config([
            'auth.guards.admin' => [
                'driver' => 'session',
                'provider' => 'admins',
            ],
            'auth.providers.admins' => [
                'driver' => 'eloquent',
                'model' => Admin::class,
            ],
            'cms.admin_redirect_after_login' => 'admin.dashboard',
            'cms.admin_login_field' => 'email',
            'cms.admin_login_attempts' => 5,
            'cms.admin_login_lockout_time' => 60,
            'cms.admin_url' => 'admin',
            'app.locale' => 'en', // Use English for tests
        ]);
        
        // Setup test routes
        $this->setupTestRoutes();
        
        // Create controller with dependencies
        $rateLimiter = new AdminRateLimiter();
        $authService = new AdminAuthService($rateLimiter);
        $this->controller = new AdminLoginController($authService, $rateLimiter);
        
        // Create admins table for testing
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobile')->nullable();
            $table->string('password');
            $table->string('role')->default('admin');
            $table->boolean('active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
            $table->string('avatar')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('locale')->default('en');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Create test admin (password will be auto-hashed by model)
        $this->testAdmin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => 'password123',
            'role' => 'admin',
            'active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('admins');
        RateLimiter::clear('admin_login:127.0.0.1:admin@test.com');
        parent::tearDown();
    }
    
    /**
     * Setup test routes for admin authentication.
     */
    protected function setupTestRoutes(): void
    {
        Route::middleware(['web'])
            ->prefix('admin')
            ->name('admin.')
            ->group(function () {
                Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
                Route::post('/login', [AdminLoginController::class, 'login'])->name('login.submit');
                Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
                Route::get('/dashboard', function () {
                    return view('dashboard');
                })->name('dashboard');
            });
    }

    public function test_show_login_form_returns_view()
    {
        $response = $this->get(route('admin.login'));
        
        $response->assertStatus(200);
        $response->assertViewIs('cms::admin.login');
        $response->assertViewHas('loginField');
        $response->assertViewHas('title');
    }

    public function test_show_login_form_redirects_authenticated_admin()
    {
        $this->actingAs($this->testAdmin, 'admin');
        
        $response = $this->get(route('admin.login'));
        
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_successful_login_with_email()
    {
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);
        
        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success');
        $this->assertTrue(Auth::guard('admin')->check());
    }

    public function test_successful_login_with_remember_me()
    {
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@test.com',
            'password' => 'password123',
            'remember' => true,
        ]);
        
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(Auth::guard('admin')->check());
        
        // In testing environment, we check that remember token was set
        $this->testAdmin->refresh();
        $this->assertNotNull($this->testAdmin->remember_token);
    }

    public function test_login_fails_with_incorrect_password()
    {
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);
        
        $response->assertSessionHasErrors('email');
        $this->assertFalse(Auth::guard('admin')->check());
    }

    public function test_login_fails_with_nonexistent_email()
    {
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'nonexistent@test.com',
            'password' => 'password123',
        ]);
        
        $response->assertSessionHasErrors('email');
        $this->assertFalse(Auth::guard('admin')->check());
    }

    public function test_login_fails_with_inactive_admin()
    {
        $this->testAdmin->update(['active' => false]);
        
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);
        
        $response->assertSessionHasErrors('email');
        $this->assertFalse(Auth::guard('admin')->check());
    }

    public function test_login_validation_requires_email()
    {
        $response = $this->post(route('admin.login.submit'), [
            'password' => 'password123',
        ]);
        
        $response->assertSessionHasErrors('email');
    }

    public function test_login_validation_requires_password()
    {
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@test.com',
        ]);
        
        $response->assertSessionHasErrors('password');
    }

    public function test_login_validation_email_format()
    {
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'invalid-email-format',
            'password' => 'password123',
        ]);
        
        $response->assertSessionHasErrors('email');
    }

    public function test_login_validation_password_min_length()
    {
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@test.com',
            'password' => '123', // Too short
        ]);
        
        $response->assertSessionHasErrors('password');
    }

    public function test_rate_limiting_blocks_too_many_attempts()
    {
        $loginData = [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ];
        
        // Make 5 failed attempts (the limit)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('admin.login.submit'), $loginData);
            $response->assertSessionHasErrors('email');
        }
        
        // 6th attempt should be rate limited
        $response = $this->post(route('admin.login.submit'), $loginData);
        $response->assertSessionHasErrors('email');
        
        // The error should mention throttling (accept both English and Persian)
        $errors = session('errors');
        $errorMessage = $errors->get('email')[0];
        $this->assertTrue(
            str_contains($errorMessage, 'throttle') || 
            str_contains($errorMessage, 'تلاش') || 
            str_contains($errorMessage, 'ثانیه'),
            'Error message should contain throttling information'
        );
    }

    public function test_successful_login_updates_last_login_data()
    {
        $this->post(route('admin.login.submit'), [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);
        
        $this->testAdmin->refresh();
        $this->assertNotNull($this->testAdmin->last_login_at);
        $this->assertNotNull($this->testAdmin->last_login_ip);
    }

    public function test_logout_destroys_session()
    {
        $this->actingAs($this->testAdmin, 'admin');
        
        $response = $this->post(route('admin.logout'));
        
        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHas('message');
        $this->assertFalse(Auth::guard('admin')->check());
    }

    public function test_login_with_redirect_parameter()
    {
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@test.com',
            'password' => 'password123',
            'redirect' => '/admin/users',
        ]);
        
        $response->assertRedirect('/admin/users');
    }

    public function test_json_response_for_ajax_login()
    {
        $response = $this->postJson(route('admin.login.submit'), [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => trans('auth.login_successful'),
        ]);
        $response->assertJsonStructure(['success', 'message', 'redirect']);
    }

    public function test_json_response_for_failed_ajax_login()
    {
        $response = $this->postJson(route('admin.login.submit'), [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);
        
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertJsonStructure(['success', 'message', 'errors']);
    }

    public function test_admin_login_request_helper_methods()
    {
        $request = AdminLoginRequest::create('/admin/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
            'redirect' => '/custom-redirect'
        ]);
        
        $this->assertEquals('email', $request->getLoginField());
        $this->assertTrue($request->shouldRemember());
        $this->assertEquals('/custom-redirect', $request->getRedirectUrl());
        
        $credentials = $request->getCredentials();
        $this->assertEquals('test@example.com', $credentials['email']);
        $this->assertEquals('password123', $credentials['password']);
        $this->assertEquals(1, $credentials['active']);
    }

    public function test_admin_model_password_hashing()
    {
        $admin = new Admin([
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => 'plainpassword'
        ]);
        
        $admin->save();
        
        // Password should be hashed
        $this->assertTrue(Hash::check('plainpassword', $admin->password));
        $this->assertNotEquals('plainpassword', $admin->password);
    }

    public function test_admin_model_active_scope()
    {
        // Create inactive admin
        Admin::create([
            'name' => 'Inactive Admin',
            'email' => 'inactive@test.com',
            'password' => 'password123',
            'active' => false,
        ]);
        
        $activeAdmins = Admin::active()->get();
        $allAdmins = Admin::all();
        
        $this->assertCount(1, $activeAdmins); // Only the test admin is active
        $this->assertCount(2, $allAdmins); // Both admins exist
    }

    public function test_admin_model_has_role_method()
    {
        $this->testAdmin->update(['role' => 'super_admin']);
        
        $this->assertTrue($this->testAdmin->hasRole('super_admin'));
        $this->assertFalse($this->testAdmin->hasRole('admin'));
        $this->assertTrue($this->testAdmin->isSuperAdmin());
    }

    public function test_admin_model_avatar_url_generation()
    {
        $avatarUrl = $this->testAdmin->getAvatarUrl();
        
        $this->assertStringContainsString('gravatar.com', $avatarUrl);
        $this->assertStringContainsString(md5('admin@test.com'), $avatarUrl);
    }
}
