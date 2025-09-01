<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Export;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use RMS\Core\Contracts\Data\UseDatabase;
use RMS\Core\Contracts\Export\ShouldExport;
use RMS\Core\Contracts\List\HasList;
use RMS\Core\Data\Export;
use RMS\Core\View\HelperList\Generator;
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
            
            $exporter = new Export($generator->builder()->sql, $this->getListFields());
            
            return $exporter->download($filename);
            
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
            
            $exporter = new Export($generator->builder()->sql, $this->getListFields());
            
            return $exporter->download($filename);
            
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
            
            // Filter fields to only include requested columns
            $filteredFields = $this->filterFieldsByColumns($this->getListFields(), $columns);
            
            $exporter = new Export($generator->builder()->sql, $filteredFields);
            
            return $exporter->download($filename);
            
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
     * Apply filters to export generator.
     *
     * @param Generator $generator
     * @param array $filters
     * @return void
     */
    protected function applyExportFilters(Generator $generator, array $filters): void
    {
        // Apply filters logic here - this should be implemented based on your Generator class
        // For now, this is a placeholder for the filtering logic
    }

    /**
     * Filter fields by requested columns.
     *
     * @param array $fields
     * @param array $columns
     * @return array
     */
    protected function filterFieldsByColumns(array $fields, array $columns): array
    {
        return array_filter($fields, function ($field) use ($columns) {
            return in_array($field['key'] ?? $field['name'] ?? '', $columns);
        });
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
