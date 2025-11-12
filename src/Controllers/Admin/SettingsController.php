<?php

namespace RMS\Core\Controllers\Admin;

use Illuminate\Http\Request;
use RMS\Core\Contracts\Form\HasForm;
use RMS\Core\Contracts\List\HasList;
use RMS\Core\Data\Field;
use RMS\Core\Models\Setting;
use Illuminate\Http\RedirectResponse;
use RMS\Core\Controllers\Admin\ProjectAdminController;

/**
 * SettingsController for managing application settings.
 * 
 * Simple key-value settings management with caching support.
 */
class SettingsController extends ProjectAdminController implements
    HasList,
    HasForm
{
    /**
     * Constructor.
     */
    public function __construct(\Illuminate\Filesystem\Filesystem $filesystem)
    {
        parent::__construct($filesystem);

        // Set the page title
        $this->title(trans('admin.settings_management'));
    }

    /**
     * Return the table name for data retrieval.
     *
     * @return string
     */
    public function table(): string
    {
        return 'settings';
    }

    /**
     * Return the model class name.
     *
     * @return string
     */
    public function modelName(): string
    {
        return Setting::class;
    }

    /**
     * Get the base route name for this controller.
     *
     * @return string
     */
    public function baseRoute(): string
    {
        return 'settings';
    }

    /**
     * Get the route parameter name.
     *
     * @return string
     */
    public function routeParameter(): string
    {
        return 'setting';
    }

    /**
     * Define the fields for the settings list.
     *
     * @return array
     */
    public function getListFields(): array
    {
        return [
            Field::make('id', 'id')->withTitle(trans('admin.id'))
                ->sortable(),

            Field::make('key', 'key')->withTitle(trans('admin.setting_key'))
                ->sortable()
                ->searchable(),

            Field::make('value', 'value')->withTitle(trans('admin.setting_value'))
                ->searchable()
                ->customMethod('renderSettingValue'),

            Field::make('updated_at', 'updated_at')->withTitle(trans('admin.updated_at'))
                ->type(Field::DATE_TIME)
                ->sortable(),
        ];
    }

    /**
     * Render setting value with truncation for long values.
     *
     * @param object $setting
     * @return string
     */
    public function renderSettingValue($setting): string
    {
        $value = $setting->value ?? '';
        
        // Truncate long values
        if (strlen($value) > 100) {
            $value = substr($value, 0, 100) . '...';
        }
        
        // Escape HTML
        $value = htmlspecialchars($value);
        
        return '<span class="text-muted" title="' . htmlspecialchars($setting->value ?? '') . '">' . $value . '</span>';
    }

    /**
     * Get the form fields for setting creation/editing.
     *
     * @return array
     */
    public function getFieldsForm(): array
    {
        return [
            Field::make('key', 'key')->withTitle(trans('admin.setting_key'))
                ->type(Field::STRING)
                ->withPlaceHolder(trans('admin.enter_setting_key'))
                ->withHint(trans('admin.setting_key_hint'))
                ->required(),

            Field::textarea('value', trans('admin.setting_value'), 5)
                ->withPlaceHolder(trans('admin.enter_setting_value'))
                ->withHint(trans('admin.setting_value_hint'))
                ->required(),
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
            'key' => 'required|string|max:255|unique:settings,key',
            'value' => 'required|string',
        ];

        // If editing, exclude current record from key uniqueness
        if (request()->route('setting')) {
            $settingId = request()->route('setting');
            $rules['key'] = 'required|string|max:255|unique:settings,key,' . $settingId;
        }

        return $rules;
    }

    /**
     * Get the list configuration.
     *
     * @return array
     */
    public function getListConfig(): array
    {
        return [
            'per_page' => 15,
            'view_id' => true,
            'create_button' => true,
            'identifier' => 'id',
            'order_by' => 'key',
            'order_way' => 'ASC'
        ];
    }

    /**
     * Hook called after adding new setting to clear cache.
     *
     * @param Request $request
     * @param int|string $id
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    protected function afterAdd(Request $request, $id, $model): void
    {
        // Cache is automatically cleared by the Setting model's boot method
        // But we can add additional logic here if needed
    }

    /**
     * Hook called after updating setting to clear cache.
     *
     * @param Request $request
     * @param int|string $id
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    protected function afterUpdate(Request $request, $id, $model): void
    {
        // Cache is automatically cleared by the Setting model's boot method
        // But we can add additional logic here if needed
    }

    /**
     * Hook called before form generation.
     *
     * @param array $templateData
     * @param \RMS\Core\Data\FormResponse $generated
     * @return void
     */
    protected function beforeSendToTemplate(array &$templateData, \RMS\Core\Data\FormResponse $generated): void
    {
        parent::beforeSendToTemplate($templateData, $generated);

        $modelId = $generated->getGenerator()->getId();

        // If we're editing, make key field readonly to prevent changing it
        if ($modelId) {
            foreach ($templateData['fields'] as $field) {
                if ($field->key === 'key') {
                    $field->withAttributes([
                        'readonly' => true,
                        'class' => 'form-control-plaintext'
                    ]);
                    $field->withHint(trans('admin.setting_key_readonly_hint'));
                    break;
                }
            }
        }
    }

    /**
     * Override destroy method to handle cache clearing.
     *
     * @param Request $request
     * @param int|string $id
     * @return RedirectResponse
     */
    public function destroy(Request $request, int|string $id): RedirectResponse
    {
        try {
            $setting = Setting::findOrFail($id);
            $key = $setting->key;
            
            $result = parent::destroy($request, $id);
            
            // Additional cache clearing if needed
            // (Model boot method already handles this)
            
            return $result;
            
        } catch (\Exception $e) {
            return back()->with('error', trans('admin.delete_failed'));
        }
    }
}