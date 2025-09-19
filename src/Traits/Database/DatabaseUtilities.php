<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Database;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Database utilities trait for helper methods and getters.
 * 
 * متدهای کمکی و ابزارهای مفید برای کار با پایگاه داده
 * 
 * @package RMS\Core\Traits\Database
 */
trait DatabaseUtilities
{
    /**
     * Get query SQL string for debugging.
     *
     * @return string
     */
    public function toSql(): string
    {
        return $this->sql->toSql();
    }

    /**
     * Get query bindings for debugging.
     *
     * @return array
     */
    public function getBindings(): array
    {
        return $this->sql->getBindings();
    }

    /**
     * Get the underlying query builder for advanced usage.
     *
     * @return Builder
     */
    public function getQueryBuilder(): Builder
    {
        return $this->sql;
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Get the table alias.
     *
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * Get the fields array.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Get applied filters for debugging.
     *
     * @return array
     */
    public function getAppliedFilters(): array
    {
        return $this->appliedFilters ?? [];
    }

    /**
     * Sanitize table name by removing dangerous characters.
     *
     * @param string $table
     * @return string
     */
    protected function sanitizeTableName(string $table): string
    {
        // حذف کاراکترهای غیرمجاز
        $sanitized = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        
        // اطمینان از شروع با حرف یا _
        if (!preg_match('/^[a-zA-Z_]/', $sanitized)) {
            $sanitized = 'table_' . $sanitized;
        }
        
        return $sanitized;
    }

    /**
     * Sanitize search term for safe usage in LIKE queries.
     *
     * @param string $term
     * @return string
     */
    protected function sanitizeSearchTerm(string $term): string
    {
        // حذف کاراکترهای خطرناک و محدود کردن طول
        $term = trim($term);
        $term = substr($term, 0, 255);
        
        // Escape کردن کاراکترهای خاص LIKE
        $term = str_replace(['%', '_'], ['\\%', '\\_'], $term);
        
        // حذف کاراکترهای کنترلی
        $term = preg_replace('/[\x00-\x1F\x7F]/', '', $term);
        
        return $term;
    }

    /**
     * Get query statistics for performance monitoring.
     *
     * @return array
     */
    public function getQueryStats(): array
    {
        return [
            'table' => $this->table,
            'alias' => $this->alias,
            'fields_count' => count($this->fields),
            'filters_count' => count($this->appliedFilters ?? []),
            'security_constraints_count' => count($this->securityConstraints ?? []),
            'has_sorting' => $this->appliedSorting !== null,
            'complexity_score' => $this->currentComplexity ?? 0,
            'sql_preview' => substr($this->toSql(), 0, 200) . '...'
        ];
    }

    /**
     * Clone the database instance with same configuration.
     *
     * @return static
     */
    public function duplicate(): static
    {
        $clone = new static($this->fields, $this->table, $this->alias);
        
        // کپی کردن تنظیمات امنیتی
        if (!empty($this->securityConstraints)) {
            $clone->securityConstraints = $this->securityConstraints;
        }
        
        // کپی کردن فیلترهای اعمال شده
        if (!empty($this->appliedFilters)) {
            $clone->appliedFilters = $this->appliedFilters;
        }
        
        return $clone;
    }

    /**
     * Reset all query modifications.
     *
     * @return $this
     */
    public function reset(): self
    {
        $this->appliedFilters = [];
        $this->appliedSorting = null;
        $this->currentComplexity = 0;
        
        // بازسازی SQL query
        $this->generateSql();
        
        return $this;
    }

    /**
     * Export query configuration as array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'table' => $this->table,
            'alias' => $this->alias,
            'fields' => array_map(fn($field) => $field->key ?? 'unknown', $this->fields),
            'applied_filters' => $this->appliedFilters ?? [],
            'applied_sorting' => $this->appliedSorting,
            'security_constraints' => array_map(function($constraint) {
                return [
                    'column' => $constraint['column'],
                    'operator' => $constraint['operator'],
                    // مقدار را برای امنیت نمایش نمی‌دهیم
                    'has_value' => $constraint['value'] !== null
                ];
            }, $this->securityConstraints ?? []),
            'complexity' => $this->currentComplexity ?? 0
        ];
    }

    /**
     * Check if query has any modifications applied.
     *
     * @return bool
     */
    public function hasModifications(): bool
    {
        return !empty($this->appliedFilters) || 
               $this->appliedSorting !== null || 
               !empty($this->securityConstraints);
    }

    /**
     * Get a summary of query modifications.
     *
     * @return string
     */
    public function getModificationsSummary(): string
    {
        $parts = [];
        
        if (!empty($this->appliedFilters)) {
            $parts[] = count($this->appliedFilters) . ' filters';
        }
        
        if ($this->appliedSorting !== null) {
            $parts[] = '1 sorting rule';
        }
        
        if (!empty($this->securityConstraints)) {
            $parts[] = count($this->securityConstraints) . ' security constraints';
        }
        
        return empty($parts) ? 'No modifications' : implode(', ', $parts);
    }
}
