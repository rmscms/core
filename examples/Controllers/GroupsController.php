<?php

namespace RMS\Core\Examples\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use RMS\Core\Data\Database;
use RMS\Core\Data\Field;
use RMS\Core\Data\Column;
use RMS\Core\Form\FormGenerator;
use RMS\Core\List\ListGenerator;

/**
 * Example Groups Controller using RMS Core Data layer
 * 
 * This demonstrates how to use the new Database, FormGenerator and ListGenerator
 * classes to create a complete CRUD interface with minimal code.
 */
class GroupsController
{
    /**
     * Get form configuration for groups
     * 
     * @return array
     */
    public function getFormFields(): array
    {
        return [
            Field::make('name')
                ->setTitle('Name')
                ->setType(Field::STRING)
                ->setRequired(true)
                ->setDatabaseKey('name'),

            Field::make('protocol')
                ->setTitle('Protocol')
                ->setType(Field::SELECT)
                ->setRequired(true)
                ->setDatabaseKey('protocol_id')
                ->setSelectData($this->getProtocolOptions()),

            Field::make('active')
                ->setTitle('Status')
                ->setType(Field::BOOL)
                ->setDatabaseKey('active')
                ->setDefaultValue(true),

            Field::make('key')
                ->setTitle('Key')
                ->setType(Field::STRING)
                ->setRequired(true)
                ->setDatabaseKey('key')
                ->setValidationRules(['unique:groups,key']),

            Field::make('amount')
                ->setTitle('Amount')
                ->setType(Field::PRICE)
                ->setRequired(true)
                ->setDatabaseKey('amount')
                ->setValidationRules(['numeric', 'min:0']),

            Field::make('day')
                ->setTitle('Day')
                ->setType(Field::INTEGER)
                ->setRequired(true)
                ->setDatabaseKey('day')
                ->setValidationRules(['integer', 'min:1', 'max:365']),

            Field::make('volume')
                ->setTitle('Volume')
                ->setType(Field::NUMBER)
                ->setRequired(true)
                ->setDatabaseKey('volume')
                ->setValidationRules(['numeric', 'min:0']),
        ];
    }

    /**
     * Get list configuration for groups
     * 
     * @return array
     */
    public function getListFields(): array
    {
        return [
            Field::make('id')
                ->setTitle('ID')
                ->setType(Field::INTEGER)
                ->setDatabaseKey('id')
                ->setWidth(80)
                ->setSortable(true),

            Field::make('name')
                ->setTitle('Name')
                ->setType(Field::STRING)
                ->setDatabaseKey('name')
                ->setSortable(true)
                ->setSearchable(true),

            Field::make('key')
                ->setTitle('Key')
                ->setType(Field::STRING)
                ->setDatabaseKey('key')
                ->setSortable(true)
                ->setSearchable(true),

            Field::make('protocol_name')
                ->setTitle('Protocol')
                ->setType(Field::STRING)
                ->setDatabaseKey('protocols.name')
                ->setMethodSql(true)
                ->setSortable(true),

            Field::make('amount')
                ->setTitle('Amount')
                ->setType(Field::PRICE)
                ->setDatabaseKey('amount')
                ->setSortable(true),

            Field::make('day')
                ->setTitle('Day')
                ->setType(Field::INTEGER)
                ->setDatabaseKey('day')
                ->setSortable(true),

            Field::make('volume')
                ->setTitle('Volume')
                ->setType(Field::NUMBER)
                ->setDatabaseKey('volume')
                ->setSortable(true),

            Field::make('active')
                ->setTitle('Status')
                ->setType(Field::BOOL)
                ->setDatabaseKey('active')
                ->setFilterable(true)
                ->setSortable(true),

            Field::make('created_at')
                ->setTitle('Created At')
                ->setType(Field::DATE_TIME)
                ->setDatabaseKey('created_at')
                ->setSortable(true),
        ];
    }

    /**
     * Generate and return list data with filtering and pagination
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getList(Request $request): JsonResponse
    {
        try {
            // Create Database instance with list fields
            $database = new Database($this->getListFields(), 'groups');
            
            // Add join for protocol names
            $database->leftJoin('protocols', 'groups.protocol_id', '=', 'protocols.id');
            
            // Apply security constraints (only active protocols)
            $database->addSecurityConstraint('groups.deleted_at', 'IS NULL', null);
            
            // Apply filters from request
            $this->applyFilters($database, $request);
            
            // Apply search if provided
            if ($request->has('search') && !empty($request->get('search'))) {
                $database->search($request->get('search'), ['groups.name', 'groups.key', 'protocols.name']);
            }
            
            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'DESC');
            $database->sort($sortBy, $sortDirection);
            
            // Get paginated results
            $perPage = min($request->get('per_page', 15), 100);
            $results = $database->get($perPage);
            
            // Generate list using ListGenerator
            $listGenerator = new ListGenerator($this->getListFields(), $results);
            
            return response()->json([
                'success' => true,
                'data' => $listGenerator->render(),
                'pagination' => [
                    'current_page' => $results->currentPage(),
                    'total' => $results->total(),
                    'per_page' => $results->perPage(),
                    'last_page' => $results->lastPage(),
                ],
                'debug' => [
                    'sql' => $database->toSql(),
                    'bindings' => $database->getBindings(),
                    'filters_applied' => $database->getAppliedFilters(),
                    'sorting_applied' => $database->getAppliedSorting(),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving groups list',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate and return form structure
     * 
     * @param Request $request
     * @param int|null $id Group ID for editing
     * @return JsonResponse
     */
    public function getForm(Request $request, int $id = null): JsonResponse
    {
        try {
            // Create FormGenerator with form fields
            $formGenerator = new FormGenerator($this->getFormFields());
            
            // If editing, load existing data
            $existingData = [];
            if ($id) {
                $database = new Database($this->getFormFields(), 'groups');
                $database->where('id', '=', $id);
                $results = $database->get(1);
                
                if ($results->count() > 0) {
                    $existingData = $results->first()->toArray();
                }
            }
            
            // Set form configuration
            $formGenerator
                ->setFormAction($id ? "groups/{$id}" : 'groups')
                ->setFormMethod($id ? 'PUT' : 'POST')
                ->setExistingData($existingData);
                
            // Add custom form validation rules
            $formGenerator->setValidationRules($this->getValidationRules());
            
            return response()->json([
                'success' => true,
                'form' => $formGenerator->render(),
                'data' => $existingData,
                'meta' => [
                    'is_edit' => !empty($id),
                    'form_id' => 'groups-form',
                    'csrf_token' => csrf_token(),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating form',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply filters from request to database query
     * 
     * @param Database $database
     * @param Request $request
     * @return void
     */
    protected function applyFilters(Database $database, Request $request): void
    {
        $filters = [];
        
        // Active status filter
        if ($request->has('active') && $request->get('active') !== '') {
            $filters[] = new Column('groups.active', '=', (bool)$request->get('active'), Field::BOOL);
        }
        
        // Protocol filter
        if ($request->has('protocol_id') && !empty($request->get('protocol_id'))) {
            $filters[] = new Column('groups.protocol_id', '=', $request->get('protocol_id'), Field::INTEGER);
        }
        
        // Amount range filter
        if ($request->has('min_amount') && is_numeric($request->get('min_amount'))) {
            $filters[] = new Column('groups.amount', '>=', $request->get('min_amount'), Field::PRICE);
        }
        
        if ($request->has('max_amount') && is_numeric($request->get('max_amount'))) {
            $filters[] = new Column('groups.amount', '<=', $request->get('max_amount'), Field::PRICE);
        }
        
        // Date range filter
        if ($request->has('created_from') && !empty($request->get('created_from'))) {
            $filters[] = new Column('groups.created_at', '>=', $request->get('created_from'), Field::DATE);
        }
        
        if ($request->has('created_to') && !empty($request->get('created_to'))) {
            $filters[] = new Column('groups.created_at', '<=', $request->get('created_to'), Field::DATE);
        }
        
        // Multiple protocol IDs filter (for advanced filtering)
        if ($request->has('protocol_ids') && is_array($request->get('protocol_ids'))) {
            $filters[] = new Column('groups.protocol_id', 'IN', $request->get('protocol_ids'), Field::INTEGER);
        }
        
        // Apply all filters
        if (!empty($filters)) {
            $database->withFilters($filters);
        }
    }

    /**
     * Get protocol options for select field
     * 
     * @return array
     */
    protected function getProtocolOptions(): array
    {
        // This would typically come from a Protocol model or service
        return [
            ['value' => 1, 'label' => 'HTTP'],
            ['value' => 2, 'label' => 'HTTPS'],
            ['value' => 3, 'label' => 'FTP'],
            ['value' => 4, 'label' => 'SFTP'],
        ];
    }

    /**
     * Get validation rules for form
     * 
     * @return array
     */
    protected function getValidationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'protocol' => ['required', 'integer', 'exists:protocols,id'],
            'key' => ['required', 'string', 'max:100', 'unique:groups,key'],
            'amount' => ['required', 'numeric', 'min:0'],
            'day' => ['required', 'integer', 'min:1', 'max:365'],
            'volume' => ['required', 'numeric', 'min:0'],
            'active' => ['boolean'],
        ];
    }

    /**
     * Advanced filtering example with complex conditions
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getAdvancedList(Request $request): JsonResponse
    {
        try {
            // Create Database instance with advanced fields
            $database = new Database($this->getListFields(), 'groups');
            
            // Complex joins
            $database
                ->leftJoin('protocols', 'groups.protocol_id', '=', 'protocols.id')
                ->leftJoin('group_stats', 'groups.id', '=', 'group_stats.group_id');
            
            // Security constraints
            $database
                ->addSecurityConstraint('groups.deleted_at', 'IS NULL', null)
                ->addSecurityConstraint('groups.active', '=', 1);
            
            // Complex filtering example
            $filters = [];
            
            // Date range with proper validation
            if ($request->has('date_range')) {
                $dateRange = $request->get('date_range');
                if (isset($dateRange['from']) && isset($dateRange['to'])) {
                    $database->whereDateBetween('groups.created_at', $dateRange['from'], $dateRange['to']);
                }
            }
            
            // Advanced search across multiple columns
            if ($request->has('advanced_search')) {
                $searchTerm = $request->get('advanced_search');
                $database->search($searchTerm, [
                    'groups.name', 
                    'groups.key', 
                    'protocols.name',
                    'groups.description'
                ]);
            }
            
            // Multi-column sorting
            if ($request->has('multi_sort')) {
                $sortRules = [
                    ['groups.active', 'DESC'],
                    ['groups.amount', 'DESC'],
                    ['groups.created_at', 'DESC']
                ];
                $database->multiSort($sortRules);
            }
            
            // Grouping and aggregation
            if ($request->has('group_by_protocol')) {
                $database
                    ->groupBy(['protocols.name'])
                    ->having('COUNT(groups.id)', '>', 0);
            }
            
            // Get results with pagination
            $results = $database->get($request->get('per_page', 15));
            
            // Generate list with additional metadata
            $listGenerator = new ListGenerator($this->getListFields(), $results);
            
            return response()->json([
                'success' => true,
                'data' => $listGenerator->render(),
                'pagination' => [
                    'current_page' => $results->currentPage(),
                    'total' => $results->total(),
                    'per_page' => $results->perPage(),
                    'last_page' => $results->lastPage(),
                ],
                'statistics' => [
                    'total_active' => $this->getTotalActive(),
                    'total_amount' => $this->getTotalAmount(),
                    'protocol_distribution' => $this->getProtocolDistribution(),
                ],
                'debug' => [
                    'sql' => $database->toSql(),
                    'bindings' => $database->getBindings(),
                    'filters_applied' => $database->getAppliedFilters(),
                    'sorting_applied' => $database->getAppliedSorting(),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving advanced groups list',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Create/Update form with comprehensive validation
     * 
     * @param Request $request
     * @param int|null $id
     * @return JsonResponse
     */
    public function saveForm(Request $request, int $id = null): JsonResponse
    {
        try {
            // Generate form for validation
            $formGenerator = new FormGenerator($this->getFormFields());
            
            // Validate request data
            $validated = $formGenerator->validate($request->all());
            
            if (!$validated['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validated['errors']
                ], 422);
            }
            
            // Use Database class for save operation
            $database = new Database($this->getFormFields(), 'groups');
            
            if ($id) {
                // Update existing record
                $database->where('id', '=', $id);
                $result = $database->update($validated['data']);
                $message = 'Group updated successfully';
            } else {
                // Create new record
                $result = $database->insert($validated['data']);
                $message = 'Group created successfully';
                $id = $result; // Assuming insert returns the new ID
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'id' => $id,
                    'redirect_url' => route('admin.groups.index')
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving group',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Performance example with optimizations
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getOptimizedList(Request $request): JsonResponse
    {
        try {
            // Create optimized fields (only what we need)
            $optimizedFields = [
                Field::make('id')->setDatabaseKey('groups.id'),
                Field::make('name')->setDatabaseKey('groups.name'),
                Field::make('active')->setDatabaseKey('groups.active'),
                Field::make('total_amount')->setDatabaseKey('SUM(groups.amount)')->setMethodSql(true),
                Field::make('protocol_count')->setDatabaseKey('COUNT(DISTINCT groups.protocol_id)')->setMethodSql(true),
            ];
            
            $database = new Database($optimizedFields, 'groups');
            
            // Optimized query with grouping
            $database
                ->join('protocols', 'groups.protocol_id', '=', 'protocols.id')
                ->where('groups.active', '=', 1)
                ->whereNotNull('groups.amount')
                ->groupBy(['groups.id', 'groups.name', 'groups.active'])
                ->having('SUM(groups.amount)', '>', 0)
                ->sort('total_amount', 'DESC')
                ->limit(50); // Limit for performance
            
            $results = $database->get(25);
            
            return response()->json([
                'success' => true,
                'data' => $results->items(),
                'meta' => [
                    'optimized' => true,
                    'query_time' => $this->getQueryTime(),
                    'memory_usage' => memory_get_peak_usage(true)
                ],
                'debug' => [
                    'sql' => $database->toSql(),
                    'performance_hints' => [
                        'uses_grouping' => true,
                        'has_joins' => true,
                        'limited_results' => true
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving optimized list',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics for dashboard
     * 
     * @return array
     */
    protected function getTotalActive(): int
    {
        $database = new Database([Field::make('count')->setDatabaseKey('COUNT(*)')], 'groups');
        $database->where('active', '=', 1);
        $result = $database->get(1);
        return $result->first()->count ?? 0;
    }

    /**
     * Get total amount across all groups
     * 
     * @return float
     */
    protected function getTotalAmount(): float
    {
        $database = new Database([Field::make('total')->setDatabaseKey('SUM(amount)')], 'groups');
        $database->where('active', '=', 1);
        $result = $database->get(1);
        return $result->first()->total ?? 0.0;
    }

    /**
     * Get protocol distribution statistics
     * 
     * @return array
     */
    protected function getProtocolDistribution(): array
    {
        $fields = [
            Field::make('protocol_name')->setDatabaseKey('protocols.name'),
            Field::make('group_count')->setDatabaseKey('COUNT(groups.id)')->setMethodSql(true),
            Field::make('total_amount')->setDatabaseKey('SUM(groups.amount)')->setMethodSql(true),
        ];
        
        $database = new Database($fields, 'groups');
        $database
            ->join('protocols', 'groups.protocol_id', '=', 'protocols.id')
            ->where('groups.active', '=', 1)
            ->groupBy(['protocols.id', 'protocols.name'])
            ->sort('group_count', 'DESC');
            
        $results = $database->get(100);
        
        return $results->map(function ($item) {
            return [
                'protocol' => $item->protocol_name,
                'count' => $item->group_count,
                'amount' => $item->total_amount
            ];
        })->toArray();
    }

    /**
     * Get query execution time (placeholder)
     * 
     * @return float
     */
    protected function getQueryTime(): float
    {
        // This would typically be measured using Laravel's query log
        return microtime(true);
    }

    /**
     * Export example using Database class
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function exportGroups(Request $request): JsonResponse
    {
        try {
            // Create export-specific fields
            $exportFields = [
                Field::make('id')->setDatabaseKey('groups.id'),
                Field::make('name')->setDatabaseKey('groups.name'),
                Field::make('key')->setDatabaseKey('groups.key'),
                Field::make('protocol_name')->setDatabaseKey('protocols.name'),
                Field::make('amount')->setDatabaseKey('groups.amount'),
                Field::make('day')->setDatabaseKey('groups.day'),
                Field::make('volume')->setDatabaseKey('groups.volume'),
                Field::make('active_status')->setDatabaseKey('CASE WHEN groups.active = 1 THEN "Active" ELSE "Inactive" END')->setMethodSql(true),
                Field::make('created_date')->setDatabaseKey('DATE(groups.created_at)')->setMethodSql(true),
            ];
            
            $database = new Database($exportFields, 'groups');
            $database
                ->leftJoin('protocols', 'groups.protocol_id', '=', 'protocols.id')
                ->where('groups.deleted_at', 'IS NULL', null)
                ->sort('groups.created_at', 'DESC');
            
            // Apply the same filters as list
            $this->applyFilters($database, $request);
            
            // Get all results for export (be careful with large datasets)
            $results = $database->get(10000); // Large limit for export
            
            return response()->json([
                'success' => true,
                'data' => $results->items(),
                'meta' => [
                    'total_records' => $results->total(),
                    'export_timestamp' => now()->toISOString(),
                    'format' => 'json'
                ],
                'debug' => [
                    'sql' => $database->toSql(),
                    'export_size' => count($results->items())
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting groups',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
