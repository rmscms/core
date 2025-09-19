<?php

namespace RMS\Core\Http\Controllers\Admin;

use RMS\Core\Contracts\Filter\ShouldFilter;
use RMS\Core\Controllers\Admin\AdminController;
use RMS\Core\Models\BugLog;
use RMS\Core\Helpers\BugLogger;
use RMS\Core\Data\Field;
use RMS\Core\Contracts\List\HasList;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
/**
 * Bug Log Admin Controller
 *
 * کنترلر مدیریت سیستم پیگیری باگ‌ها
 *
 * Features:
 * - Advanced filtering by status, severity, category
 * - Search functionality
 * - Confirm/Reject AI fixes
 * - View detailed bug information
 * - Statistics and reporting
 */
class BugLogController extends AdminController implements HasList,ShouldFilter
{

    /**
     * The model class name
     */
    protected string $modelClass = BugLog::class;

    /**
     * Return the table name for data retrieval
     */
    public function table(): string
    {
        return (new BugLog())->getTable();
    }

    /**
     * Return the model class name
     */
    public function modelName(): string
    {
        return BugLog::class;
    }

    /**
     * Get the base route of the controller (بدون prefix)
     */
    public function baseRoute(): string
    {
        return 'bug-logs';
    }

    /**
     * Get the route parameter for URLs (از resource route)
     */
    public function routeParameter(): string
    {
        return 'bug_log';  // Laravel resource routes use underscores
    }

    /**
     * Get the list configuration (از HasList interface)
     */
    public function getListConfig(): array
    {
        return [
            'per_page' => 20,
            'show_filters' => true,
            'show_search' => true,
            'show_export' => true,
            'show_create' => false  // برای bug logs فرم create نداریم
        ];
    }

    /**
     * View prefix for templates
     */
    protected string $viewPrefix = 'cms::admin.bug-logs';

    /**
     * Route prefix
     */
    protected string $routePrefix = 'admin.bug-logs';

    /**
     * Page title
     */
    protected string $pageTitle = 'مدیریت باگ‌ها';

    // ================================
    // List Configuration
    // ================================

    /**
     * Get the list fields (از HasList interface)
     */
    public function getListFields(): array
    {
        return [
            Field::make('id')->withTitle('ID')
                ->type(Field::INTEGER)
                ->sortable(),

            Field::make('title')->withTitle('عنوان خطا')
                ->type(Field::STRING)
                ->searchable()
                ->sortable(),

            Field::make('severity')->withTitle('شدت')
                ->type(Field::STRING)
                ->filterType(Field::SELECT)
                ->filterable()
                ->sortable()
                ->setOptions([
                    'CRITICAL' => 'بحرانی',
                    'HIGH' => 'بالا',
                    'MEDIUM' => 'متوسط',
                    'LOW' => 'پایین'
                ])
                ->customMethod('renderSeverity'),

            Field::make('category')->withTitle('دسته‌بندی')
                ->type(Field::STRING)
                ->filterType(Field::SELECT)
                ->filterable()
                ->sortable()
                ->setOptions([
                    'Form' => 'فرم',
                    'Database' => 'دیتابیس',
                    'Authentication' => 'احراز هویت',
                    'Validation' => 'اعتبارسنجی',
                    'Controller' => 'کنترلر',
                    'View' => 'نمایش',
                    'General' => 'عمومی'
                ]),

            Field::make('status')->withTitle('وضعیت')
                ->type(Field::STRING)
                ->filterType(Field::SELECT)
                ->filterable()
                ->sortable()
                ->setOptions([
                    'NEW' => 'جدید',
                    'IN_PROGRESS' => 'در حال پردازش',
                    'FIXED' => 'فیکس شده',
                    'CONFIRMED' => 'تایید شده',
                    'CLOSED' => 'بسته شده'
                ])
                ->customMethod('renderStatus'),

            Field::make('file_path')->withTitle('محل خطا')
                ->type(Field::STRING)
                ->customMethod('renderErrorLocation'),

            Field::make('ai_fixed')->withTitle('AI فیکس')
                ->type(Field::BOOL)
                ->filterType(Field::BOOL)
                ->filterable(),

            Field::make('human_confirmed')->withTitle('تایید انسان')
                ->type(Field::BOOL)
                ->filterType(Field::BOOL)
                ->filterable(),

            Field::make('occurred_at')->withTitle('زمان وقوع')
                ->type(Field::DATE_TIME)
                ->filterType(Field::DATE_TIME)
                ->sortable()
                ->filterable()
        ];
    }

    // ================================
    // Custom Render Methods
    // ================================

    /**
     * Render severity with color badge
     */
    public function renderSeverity($bug): string
    {
        $colors = [
            'CRITICAL' => 'bg-danger',
            'HIGH' => 'bg-warning',
            'MEDIUM' => 'bg-info',
            'LOW' => 'bg-success'
        ];

        $colorClass = $colors[$bug->severity] ?? 'bg-secondary';
        $text = match($bug->severity) {
            'CRITICAL' => 'بحرانی',
            'HIGH' => 'بالا',
            'MEDIUM' => 'متوسط',
            'LOW' => 'پایین',
            default => 'نامشخص'
        };

        return '<span class="badge ' . $colorClass . ' bg-opacity-20 text-' . str_replace('bg-', '', $colorClass) . '">' . $text . '</span>';
    }

    /**
     * Render status with color badge
     */
    public function renderStatus($bug): string
    {
        $colors = [
            'NEW' => 'bg-danger',
            'IN_PROGRESS' => 'bg-warning',
            'FIXED' => 'bg-info',
            'CONFIRMED' => 'bg-success',
            'CLOSED' => 'bg-secondary'
        ];

        $colorClass = $colors[$bug->status] ?? 'bg-secondary';
        $text = match($bug->status) {
            'NEW' => 'جدید',
            'IN_PROGRESS' => 'در حال پردازش',
            'FIXED' => 'فیکس شده',
            'CONFIRMED' => 'تایید شده',
            'CLOSED' => 'بسته شده',
            default => 'نامشخص'
        };

        return '<span class="badge ' . $colorClass . ' bg-opacity-20 text-' . str_replace('bg-', '', $colorClass) . '">' . $text . '</span>';
    }

    /**
     * Override query method from AdminController for custom filtering
     */
    public function query(\Illuminate\Database\Query\Builder $sql): void
    {
        // Default ordering
        $sql->orderBy('occurred_at', 'desc');

        // اگر نیاز به فیلتر custom باشد اینجا اضافه کنید
        parent::query($sql);
    }

    /**
     * Hook method to customize list generator before rendering
     */
    protected function beforeGenerateList(\RMS\Core\View\HelperList\Generator &$generator): void
    {
        // Remove create and edit actions since bugs are auto-created and view-only
        $generator->removeActions('edit');
        $generator->removeActions('create');
    }


    // ================================
    // Custom Actions (فقط متدهای مخصوص Bug System)
    // ================================

    /**
     * Confirm AI fix
     */
    public function confirmFix(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        $success = BugLogger::confirmFix($id, $request->notes ?? '');

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'فیکس با موفقیت تایید شد.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'خطا در تایید فیکس.'
        ], 400);
    }

    /**
     * Reject AI fix
     */
    public function rejectFix(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:1000'
        ]);

        $success = BugLogger::rejectFix($id, $request->reason ?? '');

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'فیکس رد شد و باگ به وضعیت جدید بازگردانده شد.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'خطا در رد فیکس.'
        ], 400);
    }

}
