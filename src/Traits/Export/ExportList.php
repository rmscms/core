<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Export;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use RMS\Core\Contracts\Data\UseDatabase;
use RMS\Core\Contracts\Export\ShouldExport;
use RMS\Core\Contracts\List\HasList;
use RMS\Core\View\HelperList\Generator;
use RMS\Helper\ExcelHelper;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

/**
 * Trait for handling list export functionality.
 * 
 * @package RMS\Core\Traits\Export
 */
trait ExportList
{
    /**
     * Export the current list to Excel file.
     *
     * @param string|null $filename
     * @param string $format
     * @return BinaryFileResponse|Response
     */
    public function export(?string $filename = null, string $format = 'xlsx'): BinaryFileResponse|Response
    {
        try {
            if (!$this instanceof UseDatabase || !$this instanceof HasList) {
                throw new \InvalidArgumentException(
                    'Controller must implement ' . UseDatabase::class . ' and ' . HasList::class . ' to export lists'
                );
            }

            $generator = new Generator($this);
            $filename = $filename ?? $this->getDefaultExportFilename($format);
            
            // Get query with all filters applied
            $query = $this->buildExportQuery($generator);
            
            // Get export headers from list fields
            $headers = $this->getExportHeaders();
            
            return ExcelHelper::export($query, $filename, $headers);
            
        } catch (Throwable $e) {
            Log::error('Export failed', [
                'controller' => get_class($this),
                'filename' => $filename,
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => trans('admin.export_failed')
            ], 500);
        }
    }

    /**
     * Export filtered results to Excel file.
     *
     * @param array $filters
     * @param string|null $filename
     * @param string $format
     * @return BinaryFileResponse|Response
     */
    public function exportFiltered(array $filters, ?string $filename = null, string $format = 'xlsx'): BinaryFileResponse|Response
    {
        try {
            if (!$this instanceof UseDatabase || !$this instanceof HasList) {
                throw new \InvalidArgumentException(
                    'Controller must implement ' . UseDatabase::class . ' and ' . HasList::class . ' to export lists'
                );
            }

            $generator = new Generator($this);
            
            // Apply filters to the generator
            $this->applyExportFilters($generator, $filters);
            
            $filename = $filename ?? $this->getDefaultExportFilename($format, 'filtered');
            
            // Get query with filters applied
            $query = $this->buildExportQuery($generator);
            
            // Get export headers from list fields
            $headers = $this->getExportHeaders();
            
            return ExcelHelper::export($query, $filename, $headers);
            
        } catch (Throwable $e) {
            Log::error('Filtered export failed', [
                'controller' => get_class($this),
                'filters' => $filters,
                'filename' => $filename,
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => trans('admin.export_failed')
            ], 500);
        }
    }

    /**
     * Export specific columns to Excel file.
     *
     * @param array $columns
     * @param string|null $filename
     * @param string $format
     * @return BinaryFileResponse|Response
     */
    public function exportColumns(array $columns, ?string $filename = null, string $format = 'xlsx'): BinaryFileResponse|Response
    {
        try {
            if (!$this instanceof UseDatabase || !$this instanceof HasList) {
                throw new \InvalidArgumentException(
                    'Controller must implement ' . UseDatabase::class . ' and ' . HasList::class . ' to export lists'
                );
            }

            $generator = new Generator($this);
            $filename = $filename ?? $this->getDefaultExportFilename($format, 'custom');
            
            // Get query with columns selected
            $query = $this->buildExportQuery($generator, $columns);
            
            // Filter headers to only include requested columns
            $headers = $this->getCustomExportHeaders($columns);
            
            return ExcelHelper::export($query, $filename, $headers);
            
        } catch (Throwable $e) {
            Log::error('Column export failed', [
                'controller' => get_class($this),
                'columns' => $columns,
                'filename' => $filename,
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => trans('admin.export_failed')
            ], 500);
        }
    }

    /**
     * Get default export filename.
     *
     * @param string $format
     * @param string $suffix
     * @return string
     */
    protected function getDefaultExportFilename(string $format = 'xlsx', string $suffix = ''): string
    {
        $baseName = $this->baseRoute();
        $timestamp = now()->format('Y-m-d_H-i-s');
        
        $filename = $baseName;
        
        if ($suffix) {
            $filename .= '_' . $suffix;
        }
        
        $filename .= '_' . $timestamp . '.' . $format;
        
        return $filename;
    }

    /**
     * Build query for export with filters applied.
     *
     * @param Generator $generator
     * @param array|null $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildExportQuery(Generator $generator, ?array $columns = null): \Illuminate\Database\Eloquent\Builder
    {
        $query = $this->model()::query();
        
        // Apply filters from generator (this includes cached filters)
        $builder = $generator->builder();
        
        // Get raw SQL and bindings from the builder if needed
        // For now, apply basic filters from getAllFilters if available
        if (method_exists($this, 'getAllFilters')) {
            foreach ($this->getAllFilters() as $filter) {
                $query->where($filter->column, $filter->operator, $filter->value);
            }
        }
        
        // If specific columns requested, select only those
        if ($columns) {
            $query->select($this->mapColumnsToDatabase($columns));
        } else {
            // Select all exportable columns
            $query->select($this->getExportColumns());
        }
        
        return $query;
    }
    
    /**
     * Apply filters to export generator.
     *
     * @param Generator $generator
     * @param array $filters
     * @return void
     */
    protected function applyExportFilters(Generator $generator, array $filters): void
    {
        // Apply custom filters - this can be enhanced based on your needs
        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                // This is a simplified implementation - enhance as needed
            }
        }
    }

    /**
     * Get export headers from list fields.
     *
     * @return array
     */
    protected function getExportHeaders(): array
    {
        $headers = [];
        
        foreach ($this->getListFields() as $field) {
            // Skip fields that are hidden in export
            if (property_exists($field, 'hidden_in_export') && $field->hidden_in_export) {
                continue;
            }
            
            $headers[] = $field->title ?? $field->key ?? 'Unknown';
        }
        
        return $headers;
    }
    
    /**
     * Get export columns (database column names).
     *
     * @return array
     */
    protected function getExportColumns(): array
    {
        $columns = [];
        
        foreach ($this->getListFields() as $field) {
            // Skip fields that are hidden in export
            if (property_exists($field, 'hidden_in_export') && $field->hidden_in_export) {
                continue;
            }
            
            $columns[] = $field->database_key ?? $field->key ?? 'id';
        }
        
        return $columns;
    }
    
    /**
     * Get custom export headers for specific columns.
     *
     * @param array $requestedColumns
     * @return array
     */
    protected function getCustomExportHeaders(array $requestedColumns): array
    {
        $headers = [];
        
        foreach ($this->getListFields() as $field) {
            $fieldKey = $field->key ?? '';
            
            if (in_array($fieldKey, $requestedColumns)) {
                $headers[] = $field->title ?? $fieldKey;
            }
        }
        
        return $headers;
    }
    
    /**
     * Map display column names to database column names.
     *
     * @param array $displayColumns
     * @return array
     */
    protected function mapColumnsToDatabase(array $displayColumns): array
    {
        $dbColumns = [];
        
        foreach ($this->getListFields() as $field) {
            $fieldKey = $field->key ?? '';
            
            if (in_array($fieldKey, $displayColumns)) {
                $dbColumns[] = $field->database_key ?? $fieldKey;
            }
        }
        
        return $dbColumns ?: ['*']; // Fallback to all columns if none found
    }

    /**
     * Check if export is allowed for current user.
     *
     * @return bool
     */
    protected function canExport(): bool
    {
        if ($this instanceof ShouldExport) {
            return $this->shouldExport();
        }
        
        return true; // Default to allowing export
    }

    /**
     * Get export configuration.
     *
     * @return array
     */
    protected function getExportConfig(): array
    {
        return [
            'max_rows' => 10000,
            'timeout' => 300,
            'memory_limit' => '512M'
        ];
    }

    /**
     * Validate export request parameters.
     *
     * @param array $params
     * @return array
     */
    protected function validateExportParams(array $params): array
    {
        $config = $this->getExportConfig();
        
        return [
            'format' => $params['format'] ?? 'xlsx',
            'max_rows' => min($params['max_rows'] ?? $config['max_rows'], $config['max_rows']),
            'include_headers' => $params['include_headers'] ?? true
        ];
    }
}
