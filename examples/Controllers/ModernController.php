<?php

declare(strict_types=1);

namespace RMS\Core\Examples\Controllers;

use Illuminate\Http\Request;
use RMS\Core\Contracts\Data\UseDatabase;
use RMS\Core\Contracts\Filter\ShouldFilter;
use RMS\Core\Contracts\Form\HasForm;
use RMS\Core\Contracts\List\HasList;
use RMS\Core\Contracts\Stats\HasStats;
use RMS\Core\Controllers\Admin\AdminController;
use RMS\Core\Data\Field;
use RMS\Core\Traits\FormAndList;
use RMS\Core\View\View;

/**
 * Example modern controller using the new trait system.
 *
 * This demonstrates how to use the modernized FormAndList trait
 * with proper separation of concerns and type safety.
 */
class ModernController extends AdminController implements
    UseDatabase,
    HasList,
    HasForm,
    ShouldFilter,
    HasStats
{
    use FormAndList;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // Initialize the FormAndList trait
        $this->initializeFormAndList();

        // Set controller-specific configurations
        $this->setTitle('مدیریت کاربران')
            ->setTheme('admin')
            ->setRoutePrefix('admin.');
    }

    /**
     * Get the model class name.
     *
     * @return string
     */
    public function modelName(): string
    {
        return \App\Models\User::class;
    }

    /**
     * Get the base route name.
     *
     * @return string
     */
    public function baseRoute(): string
    {
        return 'users';
    }

    /**
     * Get the table name for queries.
     *
     * @return string
     */
    public function table(): string
    {
        return 'users';
    }

    /**
     * Get list fields configuration.
     *
     * @return array
     */
    public function getListFields(): array
    {
        return [
            Field::make('id', 'شناسه')
                ->sortable()
                ->searchable(),

            Field::make('name', 'نام')
                ->sortable()
                ->searchable(),

            Field::make('email', 'ایمیل')
                ->sortable()
                ->searchable(),

            Field::make('created_at', 'تاریخ ایجاد')
                ->sortable()
                ->displayAsDate(),

            Field::make('status', 'وضعیت')
                ->sortable()
                ->displayAsBoolean()
        ];
    }

    /**
     * Get form fields configuration.
     *
     * @return array
     */
    public function getFieldsForm(): array
    {
        return [
            Field::make('name', 'نام')
                ->required()
                ->type('text')
                ->placeholder('نام کاربر را وارد کنید'),

            Field::make('email', 'ایمیل')
                ->required()
                ->type('email')
                ->placeholder('ایمیل کاربر را وارد کنید'),

            Field::make('password', 'رمز عبور')
                ->type('password')
                ->placeholder('رمز عبور را وارد کنید'),

            Field::make('status', 'وضعیت')
                ->type('checkbox')
                ->defaultValue(1)
        ];
    }

    /**
     * Get boolean fields for processing.
     *
     * @return array
     */
    public function boolFields(): array
    {
        return ['status'];
    }

    /**
     * Get list configuration.
     *
     * @return array
     */
    public function getListConfig(): array
    {
        return [
            'per_page_options' => [15, 30, 50, 100],
            'default_per_page' => 15,
            'enable_export' => true,
            'enable_batch_actions' => true,
            'enable_filters' => true
        ];
    }

    /**
     * Get form validation rules.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . request()->input('id'),
            'password' => 'nullable|string|min:8',
            'status' => 'boolean'
        ];
    }

    /**
     * Get form configuration.
     *
     * @return array
     */
    public function getFormConfig(): array
    {
        return [
            'method' => 'POST',
            'enctype' => 'multipart/form-data',
            'class' => 'modern-form',
            'autocomplete' => 'off'
        ];
    }

    /**
     * Set form template.
     *
     * @return void
     */
    public function setTplForm(): void
    {
        $this->setFormTemplate('users.form');
    }

    /**
     * Set list template.
     *
     * @return void
     */
    public function setTplList(): void
    {
        if (!$this->view->hasTpl()) {
            $this->view->setTpl('users.index');
        }
    }

    /**
     * Custom hook before adding a user.
     *
     * @param Request $request
     * @return void
     */
    protected function beforeAdd(Request &$request): void
    {
        // Hash password if provided
        if ($request->filled('password')) {
            $request->merge([
                'password' => bcrypt($request->input('password'))
            ]);
        }

        $this->logAction('user_add_attempt', [
            'email' => $request->input('email')
        ]);
    }

    /**
     * Custom hook after adding a user.
     *
     * @param Request $request
     * @param int|string $id
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    protected function afterAdd(Request $request, int|string $id, \Illuminate\Database\Eloquent\Model $model): void
    {
        $this->logAction('user_added', [
            'user_id' => $id,
            'email' => $model->email
        ]);
    }

    /**
     * Custom hook before updating a user.
     *
     * @param Request $request
     * @param int|string $id
     * @return void
     */
    protected function beforeUpdate(Request &$request, int|string $id): void
    {
        // Hash password if provided
        if ($request->filled('password')) {
            $request->merge([
                'password' => bcrypt($request->input('password'))
            ]);
        } else {
            // Remove password from request if not provided
            $request->request->remove('password');
        }

        $this->logAction('user_update_attempt', [
            'user_id' => $id
        ]);
    }

    /**
     * Custom hook before deleting a user.
     *
     * @param int|string $id
     * @return void
     */
    protected function beforeDestroy(int|string $id): void
    {
        $this->logAction('user_delete_attempt', [
            'user_id' => $id
        ]);
    }

    /**
     * Custom filter processing for users.
     *
     * @param Request $request
     * @return void
     */
    protected function processCustomFields(Request &$request): void
    {
        // Convert Persian numbers to English
        if ($request->has('phone')) {
            $phone = $this->convertPersianNumbers($request->input('phone'));
            $request->merge(['phone' => $phone]);
        }
    }

    /**
     * Convert Persian numbers to English.
     *
     * @param string $input
     * @return string
     */
    private function convertPersianNumbers(string $input): string
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($persian, $english, $input);
    }
}
