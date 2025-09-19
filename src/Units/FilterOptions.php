<?php

declare(strict_types=1);

namespace RMS\Core\Units;

use Illuminate\Http\Request;
use RMS\Core\Data\Field;
use RMS\Core\Data\FilterDatabase;

/**
 * Utility class for processing and formatting filter options.
 */
class FilterOptions
{
    /**
     * Format options for filter dropdowns with translations.
     *
     * @param array $options
     * @param string $lang
     * @return array
     */
    public static function make(array $options, string $lang = 'admin'): array
    {
        return array_map(function ($option) use ($lang) {
            return (object)[
                'id' => $option['id'],
                'name' => trans($lang . '.' . $option['name']),
            ];
        }, $options);
    }

    /**
     * Process filters from request based on field definitions.
     *
     * @param Request $request The HTTP request containing filter data
     * @param array $fields_list Array of Field objects with filter definitions
     * @return array Array of FilterDatabase objects
     */
    public static function processFilters(Request $request, array $fields_list): array
    {
        $filters = [];
        
        if (empty($fields_list)) {
            return $filters;
        }

        foreach ($fields_list as $field) {
            // Skip if filtering is disabled for this field
            if (!$field->filter) {
                continue;
            }

            // Get the filter input value
            $value = trim((string) $request->input('filter_' . $field->key));
            
            // Skip empty values for non-date fields
            if ($value === '' && !in_array($field->filter_type, [Field::DATE, Field::TIME, Field::DATE_TIME])) {
                continue;
            }

            // Determine the database column to filter on
            $column = static::getFilterColumn($field);
            
            // Process different field types
            if (in_array($field->filter_type, [Field::DATE_TIME, Field::DATE])) {
                static::processDateFilter($request, $field, $column, $filters);
            } elseif (in_array($field->filter_type, [Field::BOOL, Field::SELECT, Field::PRICE, Field::INTEGER, Field::NUMBER])) {
                static::processExactFilter($field, $column, $value, $filters);
            } else {
                static::processTextFilter($field, $column, $value, $filters);
            }
        }

        return $filters;
    }

    /**
     * Get the appropriate database column for filtering.
     *
     * @param Field $field
     * @return string
     */
    protected static function getFilterColumn(Field $field): string
    {
        // Priority: filter_key > database_key > key
        if ($field->filter_key && $field->filter_key !== false) {
            return $field->filter_key;
        }
        
        if ($field->database_key) {
            return $field->database_key;
        }
        
        return $field->key;
    }

    /**
     * Process date/datetime filters with from and to ranges.
     *
     * @param Request $request
     * @param Field $field
     * @param string $column
     * @param array &$filters
     * @return void
     */
    protected static function processDateFilter(Request $request, Field $field, string $column, array &$filters): void
    {
        $from = trim((string) $request->input('filter_' . $field->key . '_from'));
        $to = trim((string) $request->input('filter_' . $field->key . '_to'));
        
        if (!$from && !$to) {
            return;
        }

        if ($from) {
            // Convert Persian date to Gregorian if needed
            $fromDate = static::convertDateIfNeeded($from, $field->filter_type);
            $filters['filter_' . $field->key . '_from'] = new FilterDatabase(
                $column, 
                '>=', 
                $fromDate, 
                Field::DATE
            );
        }

        if ($to) {
            // Convert Persian date to Gregorian if needed
            $toDate = static::convertDateIfNeeded($to, $field->filter_type, true);
            $filters['filter_' . $field->key . '_to'] = new FilterDatabase(
                $column, 
                '<=', 
                $toDate, 
                Field::DATE
            );
        }
    }

    /**
     * Process exact match filters (select, boolean, integer, etc.).
     *
     * @param Field $field
     * @param string $column
     * @param string $value
     * @param array &$filters
     * @return void
     */
    protected static function processExactFilter(Field $field, string $column, string $value, array &$filters): void
    {
        if ($value === '') {
            return;
        }

        // Cast value to appropriate type
        $filterValue = static::castFilterValue($value, $field->filter_type);
        
        $filters['filter_' . $field->key] = new FilterDatabase(
            $column, 
            '=', 
            $filterValue, 
            $field->filter_type
        );
    }

    /**
     * Process text-based filters (LIKE searches).
     *
     * @param Field $field
     * @param string $column
     * @param string $value
     * @param array &$filters
     * @return void
     */
    protected static function processTextFilter(Field $field, string $column, string $value, array &$filters): void
    {
        if ($value === '') {
            return;
        }

        $filters['filter_' . $field->key] = new FilterDatabase(
            $column, 
            'LIKE', 
            '%' . $value . '%', 
            Field::STRING
        );
    }

    /**
     * Convert Persian date to Gregorian if needed.
     *
     * @param string $date
     * @param int $fieldType
     * @param bool $isEndDate
     * @return string
     */
    protected static function convertDateIfNeeded(string $date, int $fieldType, bool $isEndDate = false): string
    {
        // If Persian locale is enabled, convert Persian date to Gregorian
        if (config('app.locale') === 'fa') {
            try {
                // First convert Persian/Arabic numbers to English
                $date = \RMS\Helper\changeNumberToEn($date);
                
                // Check if it's a valid Persian date and convert to Gregorian
                if (\RMS\Helper\is_valid_persian_date($date, '/')) {
                    $date = \RMS\Helper\gregorian_date($date, '/');
                } elseif (\RMS\Helper\is_valid_persian_date($date, '-')) {
                    $date = \RMS\Helper\gregorian_date($date, '-');
                }
            } catch (\Exception $e) {
                // If conversion fails, use the original date (might be already Gregorian)
                // Just convert numbers
                $date = \RMS\Helper\changeNumberToEn($date);
            }
        }

        // Add time component for date fields
        if ($fieldType === Field::DATE) {
            $time = $isEndDate ? '23:59:59' : '00:00:00';
            $date .= ' ' . $time;
        }

        return str_replace('/', '-', $date);
    }

    /**
     * Convert Persian/Arabic numbers to English using RMS Helper.
     *
     * @param string $string
     * @return string
     */
    protected static function changeNumberToEn(string $string): string
    {
        return \RMS\Helper\changeNumberToEn($string);
    }

    /**
     * Cast filter value to appropriate type.
     *
     * @param string $value
     * @param int $fieldType
     * @return mixed
     */
    protected static function castFilterValue(string $value, int $fieldType): mixed
    {
        return match ($fieldType) {
            Field::INTEGER, Field::NUMBER => (int) str_replace(',', '', $value),
            Field::PRICE => (float) str_replace(',', '', $value), // Remove commas from formatted price
            Field::BOOL => (bool) $value,
            default => $value
        };
    }
}
