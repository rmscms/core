<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RMS\Core\Contracts\Data\UseDatabase;
use Throwable;

/**
 * Trait for handling boolean field toggle operations.
 * 
 * @package RMS\Core\Traits\Actions
 */
trait BoolAction
{
    /**
     * Toggle a boolean field value.
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse|RedirectResponse
     */
    public function toggleBoolField(Request $request, int|string $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'field' => 'required|string|in:' . implode(',', $this->getBoolFields())
        ]);

        $field = $request->input('field');

        try {
            if ($this instanceof UseDatabase) {
                $model = $this->model($id);
                
                if (!$model) {
                    throw new \InvalidArgumentException("Model with ID {$id} not found");
                }

                $newValue = !$model->{$field};
                $model->update([$field => $newValue]);

                $message = trans('admin.field_updated_successfully', ['field' => $field]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'new_value' => $newValue
                    ]);
                }

                return back()->with('success', $message);
            }

            throw new \InvalidArgumentException(
                'Controller must implement ' . UseDatabase::class . ' to use BoolAction'
            );

        } catch (Throwable $e) {
            Log::error('Boolean field toggle failed', [
                'controller' => get_class($this),
                'id' => $id,
                'field' => $field,
                'error' => $e->getMessage()
            ]);

            $errorMessage = trans('admin.field_update_failed');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }

            return back()->withErrors($errorMessage);
        }
    }

    /**
     * Set a boolean field to true.
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse|RedirectResponse
     */
    public function activateBoolField(Request $request, int|string $id): JsonResponse|RedirectResponse
    {
        return $this->setBoolField($request, $id, true);
    }

    /**
     * Set a boolean field to false.
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse|RedirectResponse
     */
    public function deactivateBoolField(Request $request, int|string $id): JsonResponse|RedirectResponse
    {
        return $this->setBoolField($request, $id, false);
    }

    /**
     * Set a boolean field to a specific value.
     *
     * @param Request $request
     * @param int|string $id
     * @param bool $value
     * @return JsonResponse|RedirectResponse
     */
    protected function setBoolField(Request $request, int|string $id, bool $value): JsonResponse|RedirectResponse
    {
        $request->validate([
            'field' => 'required|string|in:' . implode(',', $this->getBoolFields())
        ]);

        $field = $request->input('field');

        try {
            if ($this instanceof UseDatabase) {
                $model = $this->model($id);
                
                if (!$model) {
                    throw new \InvalidArgumentException("Model with ID {$id} not found");
                }

                $model->update([$field => $value]);

                $message = trans('admin.field_updated_successfully', ['field' => $field]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'new_value' => $value
                    ]);
                }

                return back()->with('success', $message);
            }

            throw new \InvalidArgumentException(
                'Controller must implement ' . UseDatabase::class . ' to use BoolAction'
            );

        } catch (Throwable $e) {
            Log::error('Boolean field set failed', [
                'controller' => get_class($this),
                'id' => $id,
                'field' => $field,
                'value' => $value,
                'error' => $e->getMessage()
            ]);

            $errorMessage = trans('admin.field_update_failed');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }

            return back()->withErrors($errorMessage);
        }
    }

    /**
     * Get boolean fields for validation.
     * Override this method in your controller to define boolean fields.
     *
     * @return array
     */
    protected function getBoolFields(): array
    {
        return method_exists($this, 'boolFields') ? $this->boolFields() : [];
    }

    /**
     * Batch activate/deactivate boolean fields.
     *
     * @param Request $request
     * @param bool $value
     * @return JsonResponse|RedirectResponse
     */
    public function batchToggleBoolField(Request $request, bool $value = true): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|min:1',
            'field' => 'required|string|in:' . implode(',', $this->getBoolFields())
        ]);

        $ids = $request->input('ids', []);
        $field = $request->input('field');
        $updatedCount = 0;

        try {
            if ($this instanceof UseDatabase) {
                foreach ($ids as $id) {
                    $model = $this->model($id);
                    if ($model) {
                        $model->update([$field => $value]);
                        $updatedCount++;
                    }
                }

                $message = trans('admin.batch_field_updated', [
                    'count' => $updatedCount,
                    'field' => $field
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'updated_count' => $updatedCount
                    ]);
                }

                return back()->with('success', $message);
            }

            throw new \InvalidArgumentException(
                'Controller must implement ' . UseDatabase::class . ' to use BoolAction'
            );

        } catch (Throwable $e) {
            Log::error('Batch boolean field update failed', [
                'controller' => get_class($this),
                'ids' => $ids,
                'field' => $field,
                'value' => $value,
                'error' => $e->getMessage()
            ]);

            $errorMessage = trans('admin.batch_field_update_failed');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }

            return back()->withErrors($errorMessage);
        }
    }
}
