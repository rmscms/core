<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Actions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RMS\Core\Contracts\Data\UseDatabase;
use Throwable;

/**
 * Trait for handling delete operations on resources.
 * 
 * @package RMS\Core\Traits\Actions
 */
trait DeleteAction
{
    /**
     * Delete a specific resource.
     *
     * @param Request $request
     * @param int|string $id
     * @return RedirectResponse
     */
    public function destroy(Request $request, int|string $id): RedirectResponse
    {
        try {
            $this->performDestroy($id);
            return back()->with('success', trans('admin.deleted_successfully'));
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(trans('admin.record_not_found'));
        } catch (Throwable $e) {
            Log::error('Delete action failed', [
                'controller' => get_class($this),
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(trans('admin.delete_failed'));
        }
    }

    /**
     * Batch delete multiple resources.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function batchDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|min:1'
        ]);

        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return back()->withErrors(trans('admin.select_onerows'));
        }

        $deletedCount = 0;
        $failedCount = 0;

        DB::beginTransaction();
        
        try {
            foreach ($ids as $id) {
                try {
                    $this->performDestroy($id);
                    $deletedCount++;
                } catch (Throwable $e) {
                    $failedCount++;
                    Log::warning('Batch delete item failed', [
                        'controller' => get_class($this),
                        'id' => $id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            DB::commit();
            
            if ($failedCount === 0) {
                return back()->with('success', trans('admin.deleted_successfully'));
            }
            
            return back()->with('warning', trans('admin.batch_delete_partial_success', [
                'deleted' => $deletedCount,
                'failed' => $failedCount
            ]));
            
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Batch delete transaction failed', [
                'controller' => get_class($this),
                'ids' => $ids,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(trans('admin.batch_delete_failed'));
        }
    }

    /**
     * Perform the actual delete operation.
     *
     * @param int|string $id
     * @return void
     * @throws ModelNotFoundException
     * @throws Throwable
     */
    protected function performDestroy(int|string $id): void
    {
        $this->beforeDestroy($id);
        
        if ($this instanceof UseDatabase) {
            $model = $this->model($id);
            
            if (!$model) {
                throw new ModelNotFoundException("Model with ID {$id} not found");
            }
            
            $model->delete();
        } else {
            throw new \InvalidArgumentException(
                'Controller must implement ' . UseDatabase::class . ' to use DeleteAction'
            );
        }
        
        $this->afterDestroy($id);
    }

    /**
     * Hook method called before delete operation.
     * Override this method to add custom logic before deletion.
     *
     * @param int|string $id
     * @return void
     */
    protected function beforeDestroy(int|string $id): void
    {
        // Override in child classes
    }

    /**
     * Hook method called after delete operation.
     * Override this method to add custom logic after deletion.
     *
     * @param int|string $id
     * @return void
     */
    protected function afterDestroy(int|string $id): void
    {
        // Override in child classes
    }

    /**
     * Check if the resource can be deleted.
     *
     * @param int|string $id
     * @return bool
     */
    protected function canDelete(int|string $id): bool
    {
        return true; // Override in child classes for custom logic
    }

    /**
     * Get soft delete status if applicable.
     *
     * @param int|string $id
     * @return bool
     */
    protected function isSoftDeleted(int|string $id): bool
    {
        if ($this instanceof UseDatabase) {
            $model = $this->model($id);
            return $model && method_exists($model, 'trashed') ? $model->trashed() : false;
        }
        
        return false;
    }
}
