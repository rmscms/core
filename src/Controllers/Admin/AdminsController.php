<?php

namespace RMS\Core\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController as ProjectAdminController;
use Illuminate\Http\Request;
use RMS\Core\Contracts\Actions\ChangeBoolField;
use RMS\Core\Contracts\Batch\HasBatch;
use RMS\Core\Contracts\Export\ShouldExport;
use RMS\Core\Contracts\Filter\HasSort;
use RMS\Core\Contracts\Filter\ShouldFilter;
use RMS\Core\Contracts\Form\HasForm;
use RMS\Core\Contracts\List\HasList;
use RMS\Core\Contracts\Stats\HasStats;
use RMS\Core\Contracts\Stats\HasFormStats;
use RMS\Core\Contracts\Upload\HasUploadConfig;
use RMS\Core\Data\BatchAction;
use RMS\Core\Data\Field;
use RMS\Core\Data\StatCard;
use RMS\Core\Models\Admin;
use RMS\Core\Traits\Controllers\UploadFileControllerHelper;
use Illuminate\Support\Facades\Hash;
use RMS\Core\Data\UploadConfig;

/**
 * AdminsController for managing admin users.
 *
 * Controller for managing admin users with form and list functionality.
 */
class AdminsController extends ProjectAdminController implements
    HasList,
    HasBatch,
    ChangeBoolField,
    HasSort,
    HasForm,
    ShouldFilter,
    ShouldExport,
    HasUploadConfig,
    HasStats,
    HasFormStats
{
    use UploadFileControllerHelper;

    /**
     * Constructor.
     */
    public function __construct(\Illuminate\Filesystem\Filesystem $filesystem)
    {
        parent::__construct($filesystem);

        // Set the page title
        $this->title('مدیریت ادمین‌ها');
    }

    /**
     * Check if current admin can manage other admin.
     * Super admin (ID=1) can manage all, others can only edit themselves.
     *
     * @param int|string $adminId
     * @return bool
     */
    protected function canManageAdmin($adminId): bool
    {
        $currentAdmin = auth('admin')->user();
        
        // Super admin (ID=1) can manage all
        if ($currentAdmin->id == 1) {
            return true;
        }
        
        // Others can only manage themselves
        return $currentAdmin->id == $adminId;
    }

    /**
     * Check if current admin is super admin.
     *
     * @return bool
     */
    protected function isSuperAdmin(): bool
    {
        return auth('admin')->id() == 1;
    }

    /**
     * Return the table name for data retrieval.
     *
     * @return string
     */
    public function table(): string
    {
        return 'admins';
    }

    /**
     * Return the model class name.
     *
     * @return string
     */
    public function modelName(): string
    {
        return Admin::class;
    }

    /**
     * Get the base route name for this controller.
     *
     * @return string
     */
    public function baseRoute(): string
    {
        return 'admins';
    }

    /**
     * Get the route parameter name.
     *
     * @return string
     */
    public function routeParameter(): string
    {
        return 'admin';
    }

    /**
     * Modify the query builder instance.
     *
     * @param \Illuminate\Database\Query\Builder $sql
     * @return void
     */
    public function query(\Illuminate\Database\Query\Builder $sql): void
    {
        // Apply parent query modifications
        parent::query($sql);

        // Only show non-deleted records (soft deletes)
        $sql->whereNull('deleted_at');
    }

    /**
     * Define the fields for the admin list.
     *
     * @return array
     */
    public function getListFields(): array
    {
        return [
            Field::make('id', 'id')->withTitle('شناسه')
                ->sortable(),

            Field::make('avatar', 'avatar')->withTitle('آواتار')
                ->type(Field::IMAGE)
                ->customMethod('renderImageField')
                ->skipDatabase(),

            Field::make('name', 'name')->withTitle('نام')
                ->sortable()
                ->filterable()
                ->searchable(),

            Field::make('email', 'email')->withTitle('ایمیل')
                ->type(Field::STRING)
                ->sortable()
                ->filterable()
                ->searchable(),

            Field::make('mobile', 'mobile')->withTitle('موبایل')
                ->type(Field::STRING)
                ->sortable()
                ->filterable()
                ->searchable(),

            Field::make('role', 'role')->withTitle('نقش')
                ->type(Field::STRING)
                ->filterType(Field::SELECT)
                ->filterable()
                ->sortable()
                ->customMethod('renderAdminRole')
                ->setOptions([
                    'super_admin' => 'سوپر ادمین',
                    'admin' => 'ادمین',
                    'moderator' => 'مدیر',
                    'editor' => 'ویراستار'
                ])->advanced(),

            Field::make('active', 'active')->withTitle('وضعیت')
                ->type(Field::BOOL)
                ->filterType(Field::BOOL)
                ->filterable(),

            Field::make('last_login_at', 'last_login_at')->withTitle('آخرین ورود')
                ->type(Field::DATE_TIME)
                ->filterType(Field::DATE_TIME)
                ->sortable()
                ->filterable()
                ->customMethod('renderLastLogin'),

            Field::make('created_at', 'created_at')->withTitle('تاریخ ایجاد')
                ->type(Field::DATE_TIME)
                ->filterType(Field::DATE_TIME)
                ->sortable()
                ->filterable(),
        ];
    }

    /**
     * Render admin role with color coding.
     *
     * @param object $admin
     * @return string
     */
    public function renderAdminRole($admin): string
    {
        $roles = [
            'super_admin' => 'سوپر ادمین',
            'admin' => 'ادمین',
            'moderator' => 'مدیر',
            'editor' => 'ویراستار'
        ];

        $colors = [
            'super_admin' => 'bg-danger',
            'admin' => 'bg-primary',
            'moderator' => 'bg-warning',
            'editor' => 'bg-info'
        ];

        $roleName = $roles[$admin->role] ?? 'نامشخص';
        $colorClass = $colors[$admin->role] ?? 'bg-secondary';

        return '<span class="badge ' . $colorClass . ' bg-opacity-20 text-' . str_replace('bg-', '', $colorClass) . '">' . $roleName . '</span>';
    }

    /**
     * Render last login time.
     *
     * @param object $admin
     * @return string
     */
    public function renderLastLogin($admin): string
    {
        if (!$admin->last_login_at) {
            return '<span class="text-muted">هرگز</span>';
        }

        $date = \RMS\Helper\persian_date($admin->last_login_at, 'Y/m/d H:i');
        return '<span class="text-success">' . $date . '</span>';
    }

    /**
     * Get the form fields for admin creation/editing.
     *
     * @return array
     */
    public function getFieldsForm(): array
    {
        return [
            Field::make('name', 'name')->withTitle('نام کامل')
                ->type(Field::STRING)
                ->withPlaceHolder('نام کامل ادمین را وارد کنید')
                ->required(),

            Field::make('email', 'email')->withTitle('ایمیل')
                ->type(Field::STRING)
                ->withPlaceHolder('آدرس ایمیل را وارد کنید')
                ->withValidation(['email', 'unique:admins,email'])
                ->required(),

            Field::make('mobile', 'mobile')->withTitle('شماره موبایل')
                ->type(Field::STRING)
                ->withPlaceHolder('شماره موبایل را وارد کنید')
                ->withValidation(['regex:/^09[0-9]{9}$/'])
                ->optional(),

            Field::make('password', 'password')->withTitle('گذرواژه')
                ->type(Field::PASSWORD)
                ->withPlaceHolder('گذرواژه را وارد کنید')
                ->withHint('حداقل 8 کاراکتر')
                ->withValidation(['min:8'])
                ->required(),

            Field::make('password_confirmation', null)->withTitle('تکرار گذرواژه')
                ->type(Field::PASSWORD)
                ->withPlaceHolder('گذرواژه را مجدد وارد کنید')
                ->setDatabaseKey(null)
                ->withValidation(['same:password'])
                ->required(),

            Field::make('role', 'role')->withTitle('نقش')
                ->type(Field::SELECT)
                ->setOptions([
                    '' => 'انتخاب نقش',
                    'super_admin' => 'سوپر ادمین',
                    'admin' => 'ادمین',
                    'moderator' => 'مدیر',
                    'editor' => 'ویراستار'
                ])
                ->advanced()
                ->required(),

            Field::image('avatar', 'آواتار')
                ->withHint('فرمت: JPG, PNG - حداکثر 2MB')
                ->optional(),

            Field::make('active', 'active')->withTitle('وضعیت')
                ->type(Field::BOOL)
                ->setOptions([
                    1 => 'فعال',
                    0 => 'غیرفعال'
                ])
                ->withDefaultValue(1),
        ];
    }

    /**
     * Get validation rules for form requests.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'mobile' => 'nullable|regex:/^09[0-9]{9}$/',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:super_admin,admin,moderator,editor',
            'active' => 'boolean',
            // ✅ avatar به عنوان virtual field در نظر گرفته می‌شود
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048', // 2MB max - virtual field
        ];

        // If editing, make password optional and exclude current record from email uniqueness
        if (request()->route('admin')) {
            $adminId = request()->route('admin');
            $rules['email'] = 'required|email|unique:admins,email,' . $adminId;
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }

    /**
     * Get boolean fields that can be toggled.
     *
     * @return array
     */
    public function boolFields(): array
    {
        return ['active'];
    }

    /**
     * Get the batch actions.
     *
     * @return array
     */
    public function getBatchActions(): array
    {
        return [
            new BatchAction(
                'فعال‌سازی ادمین‌ها',
                route('admin.admins.batch.activate'),
                'btn-success'
            ),
            new BatchAction(
                'غیرفعال‌سازی ادمین‌ها',
                route('admin.admins.batch.deactivate'),
                'btn-warning'
            ),
            new BatchAction(
                'حذف ادمین‌ها',
                route('admin.admins.batch.delete'),
                'btn-danger'
            ),
        ];
    }

    /**
     * Check if a batch action can be performed.
     *
     * @param string $action
     * @return bool
     */
    public function canPerformBatchAction(string $action): bool
    {
        $allowedActions = ['activate', 'deactivate', 'delete'];
        return in_array($action, $allowedActions);
    }

    /**
     * Get statistics for admin dashboard.
     *
     * @param \Illuminate\Database\Query\Builder|null $query
     * @return StatCard[]
     */
    public function getStats(?\Illuminate\Database\Query\Builder $query = null): array
    {
        $baseQuery = $query ?? app($this->modelName())->newQuery()->whereNull('deleted_at');

        return [
            StatCard::userCount(
                (clone $baseQuery)->count(),
                'مجموع ادمین‌ها'
            )->withIcon('users')
             ->withColor('primary')
             ->withColSize('col-xl-3 col-md-6')
             ->withDescription($query ? 'بر اساس فیلتر فعال' : null),

            StatCard::userCount(
                (clone $baseQuery)->where('active', true)->count(),
                'ادمین‌های فعال'
            )->withIcon('user-check')
             ->withColor('success')
             ->withColSize('col-xl-3 col-md-6')
             ->withDescription('ادمین‌های فعال'),

            StatCard::userCount(
                (clone $baseQuery)->where('role', 'super_admin')->count(),
                'سوپر ادمین‌ها'
            )->withIcon('shield')
             ->withColor('danger')
             ->withColSize('col-xl-3 col-md-6')
             ->withDescription('دسترسی کامل'),

            StatCard::userCount(
                (clone $baseQuery)->where('active', false)->count(),
                'غیرفعال'
            )->withIcon('user-x')
             ->withColor('warning')
             ->withColSize('col-xl-3 col-md-6')
             ->withDescription('نیاز به بررسی')
        ];
    }

    /**
     * Get form statistics for edit mode.
     *
     * @param mixed $model
     * @param bool $isEditMode
     * @return StatCard[]
     */
    public function getFormStats($model = null, bool $isEditMode = false): array
    {
        if (!$isEditMode || !$model) {
            return [];
        }

        return [
            StatCard::make('تاریخ ثبت‌نام', $model->created_at ? \RMS\Helper\persian_date($model->created_at, 'Y/m/d') : 'نامعلوم')
                ->withIcon('calendar')
                ->withColor('info')
                ->withColSize('col-md-3'),

            StatCard::make('آخرین ورود', $model->last_login_at ? \RMS\Helper\persian_date($model->last_login_at, 'Y/m/d H:i') : 'هرگز')
                ->withIcon('clock')
                ->withColor('warning')
                ->withColSize('col-md-3'),

            StatCard::status(
                $model->active ? 'فعال' : 'غیرفعال',
                'وضعیت',
                $model->active ? 'success' : 'danger'
            )->withIcon($model->active ? 'user-check' : 'user-x')
             ->withColSize('col-md-3'),

            StatCard::make('نقش', $this->getRoleDisplayName($model->role))
                ->withIcon('shield')
                ->withColor($model->role === 'super_admin' ? 'danger' : 'primary')
                ->withColSize('col-md-3')
        ];
    }

    /**
     * Get display name for role.
     *
     * @param string $role
     * @return string
     */
    private function getRoleDisplayName(string $role): string
    {
        $roles = [
            'super_admin' => 'سوپر ادمین',
            'admin' => 'ادمین',
            'moderator' => 'مدیر',
            'editor' => 'ویراستار'
        ];

        return $roles[$role] ?? 'نامشخص';
    }

    /**
     * Get summary statistics.
     *
     * @param \Illuminate\Database\Query\Builder|null $query
     * @return array
     */
    public function getStatSummary(?\Illuminate\Database\Query\Builder $query = null): array
    {
        $baseQuery = $query ?? app($this->modelName())->newQuery()->whereNull('deleted_at');

        return [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('active', true)->count(),
            'inactive' => (clone $baseQuery)->where('active', false)->count(),
            'super_admins' => (clone $baseQuery)->where('role', 'super_admin')->count(),
            'admins' => (clone $baseQuery)->where('role', 'admin')->count(),
            'moderators' => (clone $baseQuery)->where('role', 'moderator')->count(),
            'editors' => (clone $baseQuery)->where('role', 'editor')->count(),
        ];
    }

    /**
     * Hook called before form generation to handle password validation in edit mode.
     *
     * @param array $templateData
     * @param \RMS\Core\Data\FormResponse $generated
     * @return void
     */
    protected function beforeSendToTemplate(array &$templateData, \RMS\Core\Data\FormResponse $generated): void
    {
        // Call parent method first
        parent::beforeSendToTemplate($templateData, $generated);

        $modelId = $generated->getGenerator()->getId();
        $isCreateMode = !$modelId;

        // 📋 فیلتر AJAX upload fields در create mode، اضافه کردن data attributes در edit mode
        $this->filterAjaxUploadFields($templateData, $isCreateMode, $modelId);

        // If we're editing (ID exists)
        if ($modelId) {
            foreach ($templateData['fields'] as $field) {
                // Make password fields optional in edit mode
                if (in_array($field->key, ['password', 'password_confirmation'])) {
                    $field->setRequired(false);
                    if ($field->key === 'password') {
                        $field->withHint('خالی بگذارید اگر نمی‌خواهید تغییر دهید');
                    }
                }

                // Update email validation to exclude current record
                if ($field->key === 'email') {
                    $currentId = $generated->getGenerator()->getId();
                    $field->withValidation(['email', "unique:admins,email,{$currentId}"]);
                }

                // 🖼️ Set existing file data for image fields
                if ($field->type === \RMS\Core\Data\Field::IMAGE) {
                    $this->setExistingFileData($field, $modelId, $templateData['model'] ?? null);
                }
            }
        }
    }

    /**
     * Hook called before adding new admin.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function beforeAdd(\Illuminate\Http\Request &$request): void
    {
        // Hash password before saving
        if ($request->has('password')) {
            $request->merge([
                'password' => Hash::make($request->input('password'))
            ]);
        }
    }

    /**
     * Hook called before updating admin.
     *
     * @param \Illuminate\Http\Request $request
     * @param int|string $id
     * @return void
     */
    protected function beforeUpdate(\Illuminate\Http\Request &$request, int|string $id): void
    {
        // Only hash password if it's provided
        if ($request->filled('password')) {
            $request->merge([
                'password' => Hash::make($request->input('password'))
            ]);
        } else {
            // Remove password from request if empty
            $request->offsetUnset('password');
            $request->offsetUnset('password_confirmation');
        }
    }

    /**
     * Get upload configuration for file fields.
     * استفاده از UploadConfig Object برای تنظیمات بهتر و قابل فهم‌تر
     *
     * @return array
     */
    public function getUploadConfig(): array
    {
        return [
            // 🎯 Avatar Configuration با UploadConfig Object
            'avatar' => UploadConfig::create('avatar')
                ->avatar() // استفاده از پیش‌تنظیم avatar
                ->path('uploads/admins/avatars')
                ->ajaxUpload(true) // ✅ فعال‌سازی AJAX upload
                ->listThumbnailSize(50, 50) // thumbnail بزرگ‌تر برای admins
        ];
    }

    public function getStatsCardExpanded() :bool
    {
        return true;
    }
    
    /**
     * Override edit method to add authorization.
     *
     * @param \Illuminate\Http\Request $request
     * @param int|string $id
     * @return \Illuminate\View\View
     */
    public function edit(\Illuminate\Http\Request $request, int|string $id)
    {
        // Authorization check
        if (!$this->canManageAdmin($id)) {
            abort(403, 'شما فقط مجاز به ویرایش پروفایل خود هستید.');
        }
        
        return parent::edit($request, $id);
    }
    
    /**
     * Override update method to add authorization.
     *
     * @param \Illuminate\Http\Request $request
     * @param int|string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(\Illuminate\Http\Request $request, int|string $id): \Illuminate\Http\RedirectResponse
    {
        // Authorization check
        if (!$this->canManageAdmin($id)) {
            abort(403, 'شما فقط مجاز به ویرایش پروفایل خود هستید.');
        }
        
        return parent::update($request, $id);
    }
    
    /**
     * Override destroy method to add authorization.
     *
     * @param \Illuminate\Http\Request $request
     * @param int|string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(\Illuminate\Http\Request $request, int|string $id): \Illuminate\Http\RedirectResponse
    {
        // Only super admin can delete
        if (!$this->isSuperAdmin()) {
            abort(403, 'فقط سوپر ادمین مجاز به حذف ادمین‌ها است.');
        }
        
        // Cannot delete yourself
        if (auth('admin')->id() == $id) {
            return back()->with('error', 'نمی‌توانید خود را حذف کنید.');
        }
        
        return parent::destroy($request, $id);
    }
    
    /**
     * Get list configuration with dynamic action buttons.
     *
     * @return array
     */
    public function getListConfig(): array
    {
        $config = [
            'per_page' => 15,
            'view_id' => true,
            'create_button' => $this->isSuperAdmin(), // فقط super admin می‌تواند admin جدید ایجاد کند
            'identifier' => 'id',
            'order_by' => 'id',
            'order_way' => 'DESC'
        ];
        
        return $config;
    }
    
    /**
     * Override to customize row actions based on permissions.
     *
     * @param \RMS\Core\View\HelperList\ListResponse $listResponse
     * @return void
     */
    protected function beforeSendToListTemplate(&$listResponse): void
    {
        parent::beforeSendToListTemplate($listResponse);
        
        // No additional modifications needed here since we're using skip arrays
        // The skip arrays in editAction() and deleteAction() handle the permissions
    }
    
    /**
     * Override edit action to add authorization check per row.
     *
     * @param \RMS\Core\View\HelperList\Generator $generator
     * @return void
     */
    protected function editAction(\RMS\Core\View\HelperList\Generator &$generator): void
    {
        $routeName = $this->prefix_route . $this->baseRoute() . '.edit';
        $action = (new \RMS\Core\Data\Action(
            trans('admin.edit'),
            $routeName,
            config($this->theme . '.actions.edit'),
            'edit btn-outline-success'
        ))->withMethod('GET');
        
        // Add IDs that should be skipped for edit action
        // If not super admin, can only edit self
        if (!$this->isSuperAdmin()) {
            $currentAdminId = auth('admin')->id();
            // Get all admin IDs except current admin
            $allAdmins = \RMS\Core\Models\Admin::whereNull('deleted_at')->pluck('id')->toArray();
            $skippedIds = array_filter($allAdmins, function($id) use ($currentAdminId) {
                return $id != $currentAdminId;
            });
            $action->withSkips($skippedIds);
        }
        
        $generator->addAction($action);
    }
    
    /**
     * Override delete action to add authorization check per row.
     *
     * @param \RMS\Core\View\HelperList\Generator $generator
     * @return void
     */
    protected function deleteAction(\RMS\Core\View\HelperList\Generator &$generator): void
    {
        $confirm = (new \RMS\Core\Data\Confirm(
            trans('admin.are_u_sure'),
            trans('admin.action_can_not_undone'),
            'warning',
            'delete'
        ))
        ->confirmButton('مطمئن هستم')
        ->cancelButton('خیر');

        $routeName = $this->prefix_route . $this->baseRoute() . '.destroy';
        $action = (new \RMS\Core\Data\Action(
            trans('admin.delete'),
            $routeName,
            config($this->theme . '.actions.destroy'),
            'delete btn-outline-danger'
        ))->withConfirm($confirm)->withMethod('DELETE');
        
        // Add IDs that should be skipped for delete action
        if ($this->isSuperAdmin()) {
            // Super admin can delete all except themselves
            $currentAdminId = auth('admin')->id();
            $action->withSkips([$currentAdminId]);
        } else {
            // Non-super admin cannot delete anyone
            $allAdmins = \RMS\Core\Models\Admin::whereNull('deleted_at')->pluck('id')->toArray();
            $action->withSkips($allAdmins);
        }

        $generator->addAction($action);

        // Add batch delete action if enabled (only for super admin)
        if ($generator->batch_destroy && $this->isSuperAdmin()) {
            $batchAction = (new \RMS\Core\Data\BatchAction('حذف دسته‌جمعی', 'destroy', 'btn-danger'))
                ->confirm($confirm);
            $generator->addBatchAction($batchAction);
        }
    }
}
