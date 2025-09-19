<?php

namespace RMS\Core\Contracts\Stats;

use RMS\Core\Data\StatCard;

/**
 * Interface for classes that provide form statistics.
 * 
 * Used to display statistical cards above forms in edit mode,
 * providing contextual information about the model being edited.
 */
interface HasFormStats
{
    /**
     * Get the statistics data for the form.
     * 
     * @param mixed $model The model instance being edited (null for create mode)
     * @param bool $isEditMode Whether we're in edit mode or create mode
     * @return StatCard[] Array of StatCard instances
     * 
     * Example usage:
     * ```php
     * public function getFormStats($model = null, bool $isEditMode = false): array
     * {
     *     if (!$isEditMode || !$model) {
     *         return [];
     *     }
     * 
     *     return [
     *         StatCard::make('آخرین لاگین', '2 روز پیش')
     *             ->withIcon('clock')
     *             ->withColor('info'),
     *             
     *         StatCard::userCount($model->orders_count, 'تعداد سفارشات')
     *             ->withIcon('shopping-bag')
     *             ->withColor('success'),
     *     ];
     * }
     * ```
     */
    public function getFormStats($model = null, bool $isEditMode = false): array;
}