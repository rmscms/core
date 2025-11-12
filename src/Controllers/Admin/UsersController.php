<?php

namespace RMS\Core\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use RMS\Core\Data\UploadConfig;
use RMS\Core\Contracts\Actions\ChangeBoolField;
use RMS\Core\Contracts\Batch\HasBatch;
use RMS\Core\Contracts\Export\ShouldExport;
use RMS\Core\Contracts\Filter\HasSort;
use RMS\Core\Contracts\Filter\ShouldFilter;
use RMS\Core\Contracts\Form\HasForm;
use RMS\Core\Contracts\List\HasList;
use RMS\Core\Contracts\Requests\RequestForm;
use RMS\Core\Contracts\Stats\HasStats;
use RMS\Core\Contracts\Stats\HasFormStats;
use RMS\Core\Contracts\Upload\HasUploadConfig;
use RMS\Core\Data\StatCard;
use RMS\Core\Data\BatchAction;
use RMS\Core\Data\Field;
use RMS\Core\Models\User;
use RMS\Core\Traits\Controllers\UploadFileControllerHelper;
use RMS\Core\Controllers\Admin\ProjectAdminController;

/**
 * Users Controller for Admin Panel.
 *
 * Controller for managing users with form and list functionality.
 */
class UsersController extends ProjectAdminController implements HasList, HasBatch, ChangeBoolField, HasSort, HasForm, ShouldFilter, ShouldExport, HasUploadConfig, HasStats, HasFormStats
{
    use UploadFileControllerHelper;
    
    /**
     * Constructor.
     */
    public function __construct(\Illuminate\Filesystem\Filesystem $filesystem)
    {
        parent::__construct($filesystem);

        // Set the page title
        $this->title(trans('admin.users_management'));
    }

    /**
     * Return the table name for data retrieval.
     *
     * @return string
     */
    public function table(): string
    {
        return 'users';
    }

    /**
     * Return the model class name.
     *
     * @return string
     */
    public function modelName(): string
    {
        return User::class;
    }

    /**
     * Get the base route name for this controller.
     *
     * @return string
     */
    public function baseRoute(): string
    {
        return 'users';
    }

    /**
     * Get the route parameter name.
     *
     * @return string
     */
    public function routeParameter(): string
    {
        return 'user';
    }

    /**
     * Define the fields for the user list.
     *
     * @return array
     */
    public function getListFields(): array
    {
        return [
            Field::make('id', 'id')->withTitle(trans('admin.id'))
                ->sortable(),

            Field::make('avatar', 'avatar')->withTitle(trans('admin.user_avatar'))
                ->type(Field::IMAGE)
                ->skipDatabase(),

            Field::make('name', 'name')->withTitle(trans('admin.user_name'))
                ->sortable()
                ->filterable()
                ->searchable(),

            Field::make('email', 'email')->withTitle(trans('admin.email_address'))
                ->type(Field::STRING)
                ->sortable()
                ->filterable()
                ->searchable(),

            Field::make('group_id', 'group_id')->withTitle(trans('admin.user_role'))
                ->type(Field::STRING)
                ->filterType(Field::SELECT)
                ->filterable()
                ->sortable()
                ->customMethod('renderUserGroup')
                ->setOptions([
                    1 => trans('admin.admin'),
                    2 => trans('admin.moderator'),
                    3 => trans('admin.editor'),
                    4 => 'مهمانان'
                ])->advanced(),

            Field::make('email_verified_at', 'email_verified_at')->withTitle('تأیید ایمیل')
                ->type(Field::BOOL)
                ->customMethod('renderEmailVerified'),

            Field::make('active', 'active')->withTitle(trans('admin.active'))
                ->type(Field::BOOL)
                ->filterType(Field::BOOL)
                ->filterable(),

            Field::make('email_notifications', 'email_notifications')->withTitle('اعلان‌های ایمیل')
                ->type(Field::BOOL)
                ->filterType(Field::BOOL)
                ->filterable(),

            Field::make('created_at', 'created_at')->withTitle(trans('admin.created_at'))
                ->type(Field::DATE_TIME)->filterType(Field::DATE_TIME)
                ->sortable()
                ->filterable(),
        ];
    }

    /**
     * Render email verification status.
     */
    public function renderEmailVerified($user): string
    {
        $isVerified = !is_null($user->email_verified_at);

        if ($isVerified) {
            return '<span class="badge bg-success bg-opacity-20 text-success">' . trans('admin.active') . '</span>';
        } else {
            return '<span class="badge bg-warning bg-opacity-20 text-warning">تأیید نشده</span>';
        }
    }

    /**
     * Render user group.
     */
    public function renderUserGroup($user): string
    {
        $groups = [
            1 => trans('admin.admin'),
            2 => trans('admin.moderator'),
            3 => trans('admin.editor'),
            4 => 'مهمانان'
        ];

        $groupId = $user->group_id ?? 0;
        $groupName = $groups[$groupId] ?? 'نامشخص';

        $colors = [
            1 => 'bg-danger',
            2 => 'bg-primary',
            3 => 'bg-success',
            4 => 'bg-info'
        ];

        $colorClass = $colors[$groupId] ?? 'bg-secondary';

        return '<span class="badge ' . $colorClass . ' bg-opacity-20 text-' . str_replace('bg-', '', $colorClass) . '">' . $groupName . '</span>';
    }

    /**
     * Get the batch actions.
     */
    public function getBatchActions(): array
    {
        return [
            new BatchAction(
                trans('admin.bulk_activate'),
                route('admin.users.batch.activate'),
                'btn-success'
            ),
            new BatchAction(
                trans('admin.bulk_deactivate'),
                route('admin.users.batch.deactivate'),
                'btn-warning'
            ),
            new BatchAction(
                trans('admin.bulk_delete'),
                route('admin.users.batch.delete'),
                'btn-danger'
            ),
        ];
    }

    /**
     * Check if a batch action can be performed.
     */
    public function canPerformBatchAction(string $action): bool
    {
        $allowedActions = ['activate', 'deactivate', 'delete'];
        return in_array($action, $allowedActions);
    }

    /**
     * Get boolean fields that can be toggled.
     */
    public function boolFields(): array
    {
        return ['active', 'email_notifications'];
    }

    /**
     * Get the form fields for user creation/editing.
     */
    public function getFieldsForm(): array
    {
        return [
            Field::make('name', 'name')->withTitle(trans('admin.user_name'))
                ->type(Field::STRING)
                ->withPlaceHolder(trans('admin.user_name'))
                ->required(),

            Field::make('email', 'email')->withTitle(trans('admin.email_address'))
                ->type(Field::STRING)
                ->withPlaceHolder(trans('admin.email_address'))
                ->required(),

            Field::make('password', 'password')->withTitle(trans('admin.password'))
                ->type(Field::PASSWORD)
                ->withPlaceHolder(trans('admin.password'))
                ->withHint(trans('admin.password_min_length', ['min' => 8]))
                ->required(),

            Field::make('password_confirmation', null)->withTitle(trans('admin.password_confirmation'))
                ->type(Field::PASSWORD)
                ->withPlaceHolder(trans('admin.password_confirmation'))
                ->setDatabaseKey(null)
                ->required(),

            Field::make('group_id', 'group_id')->withTitle(trans('admin.user_role'))
                ->type(Field::SELECT)
                ->setOptions([
                    '' => 'انتخاب گروه',
                    1 => trans('admin.admin'),
                    2 => trans('admin.moderator'),
                    3 => trans('admin.editor'),
                    4 => 'مهمانان'
                ])
                ->advanced()
                ->required(),

            Field::make('active', 'active')->withTitle(trans('admin.user_status'))
                ->type(Field::BOOL)
                ->setOptions([
                    1 => trans('admin.active'),
                    0 => trans('admin.inactive')
                ])
                ->withDefaultValue(1),

            Field::make('email_notifications', 'email_notifications')->withTitle('اعلان‌های ایمیل')
                ->type(Field::BOOL)
                ->setOptions([
                    1 => trans('admin.active'),
                    0 => trans('admin.inactive')
                ])
                ->withDefaultValue(1),

            Field::date('birth_date', trans('admin.birth_date'))
                ->withPlaceHolder('YYYY/MM/DD')
                ->withAttributes(['class' => 'persian-datepicker'])
                ->optional(),

            Field::image('avatar', trans('admin.user_avatar'), [
                'max_size' => '1MB',
                'preview' => true,
                'drag_drop' => true,
                'resize' => ['width' => 400, 'height' => 400],
                'thumbnail' => ['width' => 100, 'height' => 100]
            ])
                ->withHint(trans('admin.allowed_file_types', ['types' => 'JPG, PNG, GIF']) . ' (حداکثر 1MB)')
                ->optional(),
        ];
    }

    /**
     * Get validation rules for the form.
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'group_id' => 'required|integer|in:1,2,3,4',
            'active' => 'boolean',
            'email_notifications' => 'boolean',
            'birth_date' => 'nullable|date',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:1024',
        ];

        // If editing, make password optional and exclude current user from email uniqueness
        if (request()->route('user')) {
            $userId = request()->route('user');
            $rules['email'] = 'required|email|unique:users,email,' . $userId;
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }

    /**
     * Get upload configuration for file fields.
     */
    public function getUploadConfig(): array
    {
        return [
            'avatar' => UploadConfig::create('avatar')
                ->avatar()
                ->ajaxUpload(true)
                ->listThumbnailSize(40, 40),
        ];
    }

    /**
     * Hook called before sending template data to blade view.
     */
    protected function beforeSendToTemplate(array &$templateData, \RMS\Core\Data\FormResponse $generated): void
    {
        parent::beforeSendToTemplate($templateData, $generated);

        $modelId = $generated->getGenerator()->getId();
        $isCreateMode = !$modelId;

        $this->filterAjaxUploadFields($templateData, $isCreateMode, $modelId);

        if ($modelId) {
            foreach ($templateData['fields'] as $field) {
                if (in_array($field->key, ['password', 'password_confirmation'])) {
                    $field->setRequired(false);

                    if ($field->key === 'password') {
                        $field->withHint(trans('admin.password_empty_hint'));
                    }
                }

                if ($field->type === \RMS\Core\Data\Field::IMAGE) {
                    $this->setExistingFileData($field, $modelId, $templateData['model'] ?? null);
                }
            }
        }
    }

    /**
     * Hook called before adding a new user.
     */
    protected function beforeAdd(Request &$request): void
    {
        if (isset($request['password'])) {
            $request['password'] = Hash::make($request['password']);
        }
        unset($request['password_confirmation']);
    }

    /**
     * Hook called before updating an existing user.
     */
    protected function beforeUpdate(Request &$request, $id): void
    {
        if (!empty($request['password'])) {
            $request['password'] = Hash::make($request['password']);
        } else {
            unset($request['password']);
        }
        unset($request['password_confirmation']);
    }

    /**
     * Get the statistics data for the users.
     */
    public function getStats(\Illuminate\Database\Query\Builder $query = null): array
    {
        if (!$query) {
            $model = app($this->modelName());
            $baseQuery = $model->newQuery();
        } else {
            $baseQuery = $query;
        }
        
        $filterDescription = $query ? trans('admin.based_on_active_filters') : null;
        
        return [
            StatCard::userCount((clone $baseQuery)->count(), trans('admin.total_users'))
                ->withIcon('users')
                ->withColor('primary')
                ->withDescription($filterDescription),
                
            StatCard::userCount((clone $baseQuery)->where('active', 1)->count(), trans('admin.active_users'))
                ->withIcon('user-check')
                ->withColor('success'),
                
            StatCard::userCount((clone $baseQuery)->where('active', 0)->count(), trans('admin.inactive_users'))
                ->withIcon('user-x')
                ->withColor('danger'),
                
            StatCard::userCount((clone $baseQuery)->whereDate('created_at', today())->count(), trans('admin.new_users_today'))
                ->withIcon('calendar-plus')
                ->withColor('info'),
        ];
    }
    
    /**
     * Get a summary of statistics.
     * Implementation of HasStats interface.
     * 
     * @param \Illuminate\Database\Query\Builder|null $query Optional query builder with applied filters
     * @return array
     */
    public function getStatSummary(?\Illuminate\Database\Query\Builder $query = null): array
    {
        if (!$query) {
            $model = app($this->modelName());
            $baseQuery = $model->newQuery();
        } else {
            $baseQuery = $query;
        }
        
        $totalUsers = (clone $baseQuery)->count();
        $activeUsers = (clone $baseQuery)->where('active', 1)->count();
        
        return [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'inactive' => $totalUsers - $activeUsers,
            'today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
            'active_percentage' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 2) : 0,
            'has_filters' => $query !== null
        ];
    }

    /**
     * Get the form statistics data for user forms.
     */
    public function getFormStats($model = null, bool $isEditMode = false): array
    {
        if (!$isEditMode || !$model) {
            return [];
        }
        
        $stats = [];
        
        // User status
        $statusText = $model->active ? trans('admin.active') : trans('admin.inactive');
        $statusColor = $model->active ? 'success' : 'danger';
        $stats[] = StatCard::status($statusText, trans('admin.user_status'), $statusColor)
            ->withIcon($model->active ? 'user-check' : 'user-x');
        
        // User group
        $groups = [
            1 => trans('admin.admin'),
            2 => trans('admin.moderator'),
            3 => trans('admin.editor'),
            4 => 'مهمانان'
        ];
        $groupName = $groups[$model->group_id ?? 0] ?? 'نامشخص';
        $stats[] = StatCard::make(trans('admin.user_role'), $groupName)
            ->withIcon('users')
            ->withColor('primary');
        
        // Registration date
        if ($model->created_at) {
            $daysAgo = $model->created_at->diffInDays(now());
            $membershipText = $daysAgo === 0 ? trans('admin.today') : 
                ($daysAgo === 1 ? trans('admin.yesterday') : 
                ($daysAgo < 30 ? $daysAgo . ' روز پیش' : 
                $model->created_at->format('Y/m/d')));
            
            $stats[] = StatCard::make(trans('admin.registration_date'), $membershipText)
                ->withIcon('calendar-plus')
                ->withColor('info')
                ->withDescription($daysAgo . ' روز عضویت');
        }
        
        // Email verification status
        $emailVerified = $model->email_verified_at ? 'تأیید شده' : 'تأیید نشده';
        $emailColor = $model->email_verified_at ? 'success' : 'warning';
        $stats[] = StatCard::status($emailVerified, 'تأیید ایمیل', $emailColor)
            ->withIcon($model->email_verified_at ? 'shield-check' : 'shield-warning');
        
        return $stats;
    }
}