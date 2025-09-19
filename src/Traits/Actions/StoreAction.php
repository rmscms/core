<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RMS\Core\Contracts\Data\UseDatabase;
use RMS\Core\Contracts\List\HasList;
use RMS\Core\Requests\Store;
use Throwable;

/**
 * Trait for handling store and update operations on resources.
 *
 * @package RMS\Core\Traits\Actions
 */
trait StoreAction
{

    /**
     * Store a new resource or update existing one.
     *
     * @param Store $request
     * @return RedirectResponse
     */
    public function store(Store $request): RedirectResponse
    {
        return $this->processStore($request);
    }

    /**
     * Process store request - determine if it's add or update operation.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function processStore(Request $request): RedirectResponse
    {
        try {
            $this->prepareRequestData($request);

            $id = $request->input('id');

            return $id ? $this->performUpdate($request, $id) : $this->performAdd($request);
        } catch (Throwable $e) {
            Log::error('Store operation failed', [
                'controller' => get_class($this),
                'request_data' => $request->except(['password', 'password_confirmation']),
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->withErrors(trans('admin.save_failed'));
        }
    }

    /**
     * Update an existing resource.
     *
     * @param Request $request
     * @param int|string $id
     * @return RedirectResponse
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        try {
            $this->prepareRequestData($request);
            return $this->performUpdate($request, $id);
        } catch (Throwable $e) {
            Log::error('Update operation failed', [
                'controller' => get_class($this),
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->withErrors(trans('admin.update_failed'));
        }
    }

    /**
     * Perform the actual update operation.
     *
     * @param Request $request
     * @param int|string $id
     * @return RedirectResponse
     */
    protected function performUpdate(Request $request, int|string $id): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->beforeUpdate($request, $id);

            if ($this instanceof UseDatabase) {
                $model = $this->model($id);

                if (!$model) {
                    throw new \InvalidArgumentException("Model with ID {$id} not found");
                }

                // ✅ فیلتر کردن فیلدهای skipDatabase
                $databaseFields = $this->filterDatabaseFields($request->all());
                $model->fill($databaseFields)->save();
                $this->afterUpdate($request, $id, $model);

                DB::commit();
                return $this->getRedirectResponse($request, $id);
            }

            throw new \InvalidArgumentException(
                'Controller must implement ' . UseDatabase::class . ' to use StoreAction'
            );
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Add a new resource.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function add(Request $request): RedirectResponse
    {
        try {
            $this->prepareRequestData($request);
            return $this->performAdd($request);
        } catch (Throwable $e) {
            Log::error('Add operation failed', [
                'controller' => get_class($this),
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->withErrors(trans('admin.add_failed'));
        }
    }

    /**
     * Perform the actual add operation.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    protected function performAdd(Request $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->beforeAdd($request);

            if ($this instanceof UseDatabase) {
                $model = $this->model();
                // ✅ فیلتر کردن فیلدهای skipDatabase
                $databaseFields = $this->filterDatabaseFields($request->all());
                $model->fill($databaseFields)->save();

                $this->afterAdd($request, $model->id, $model);

                DB::commit();
                return $this->getRedirectResponse($request, $model->id);
            }

            throw new \InvalidArgumentException(
                'Controller must implement ' . UseDatabase::class . ' to use StoreAction'
            );
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Hook method called before update operation.
     *
     * @param Request $request
     * @param int|string $id
     * @return void
     */
    protected function beforeUpdate(Request &$request, int|string $id): void
    {
        // Override in child classes
    }

    /**
     * Hook method called after update operation.
     *
     * @param Request $request
     * @param int|string $id
     * @param Model $model
     * @return void
     */
    protected function afterUpdate(Request $request, int|string $id, Model $model): void
    {
        // Override in child classes
    }

    /**
     * Hook method called before add operation.
     *
     * @param Request $request
     * @return void
     */
    protected function beforeAdd(Request &$request): void
    {
        // Override in child classes
    }

    /**
     * Hook method called after add operation.
     *
     * @param Request $request
     * @param int|string $id
     * @param Model $model
     * @return void
     */
    protected function afterAdd(Request $request, int|string $id, Model $model): void
    {
        // Override in child classes
    }

    /**
     * Get redirect response after successful operation.
     *
     * @param Request $request
     * @param int|string $id
     * @return RedirectResponse
     */
    protected function getRedirectResponse(Request $request, int|string $id): RedirectResponse
    {
        // Check if "Save and Stay" button was clicked
        if ($request->has('stay_in_form') && $this instanceof HasList) {
            return redirect(route(
                'admin.' . $this->baseRoute() . '.edit',
                [$this->routeParameter() => $id]
            ))->with('success', trans('admin.success_action'));
        }

        // Default: redirect to list
        if ($this instanceof HasList) {
            return redirect(route('admin.' . $this->baseRoute() . '.index'))
                ->with('success', trans('admin.success_action'));
        }

        return back()->with('success', trans('admin.success_action'));
    }

    /**
     * Prepare request data before processing.
     *
     * @param Request $request
     * @return void
     */
    protected function prepareRequestData(Request &$request): void
    {
        // Call prepareForValidation if available (for Persian date conversion, etc.)
        if (method_exists($this, 'prepareForValidation')) {
            $this->prepareForValidation($request);
        }
        
        $this->processBooleanFields($request);
        $this->processCustomFields($request);
    }

    /**
     * Process boolean fields in the request.
     *
     * @param Request $request
     * @return void
     */
    protected function processBooleanFields(Request &$request): void
    {
        foreach ($this->getBoolFields() as $boolField) {
            $value = $request->has($boolField) ? 1 : 0;
            $request->merge([$boolField => $value]);
        }
    }

    /**
     * Process custom fields in the request.
     * Override this method to add custom field processing.
     *
     * @param Request $request
     * @return void
     */
    protected function processCustomFields(Request &$request): void
    {
        // Override in child classes
    }

    /**
     * Filter out fields that should be skipped when saving to database.
     * Checks if controller implements HasForm and filters based on field->skip_database property.
     *
     * @param array $requestData
     * @return array
     */
    protected function filterDatabaseFields(array $requestData): array
    {
        // اگر controller فرم ندارد, همه فیلدها قبول
        if (!method_exists($this, 'getFieldsForm')) {
            return $requestData;
        }

        try {
            $formFields = $this->getFieldsForm();
            $filtered = [];

            foreach ($requestData as $fieldKey => $value) {
                $shouldInclude = true;

                // جستجو فیلد در فرم
                foreach ($formFields as $field) {
                    if (isset($field->key) && $field->key === $fieldKey) {
                        // اگر فیلد skip_database دارد, حذف کن
                        if (isset($field->skip_database) && $field->skip_database === true) {
                            $shouldInclude = false;
                        }
                        break;
                    }
                }

                if ($shouldInclude) {
                    $filtered[$fieldKey] = $value;
                }
            }

            return $filtered;

        } catch (\Exception $e) {
            // در صورت خطا, همه فیلدها را برگردان
            Log::warning('Failed to filter database fields', [
                'controller' => get_class($this),
                'error' => $e->getMessage()
            ]);

            return $requestData;
        }
    }

}
