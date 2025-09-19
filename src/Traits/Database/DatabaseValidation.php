<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Database;

use InvalidArgumentException;
use RMS\Core\Data\Column;
use RMS\Core\Data\Field;

/**
 * Database validation trait for input validation and security checks.
 * 
 * اعتبارسنجی ورودی‌ها و بررسی‌های امنیتی برای پایگاه داده
 * 
 * @package RMS\Core\Traits\Database
 */
trait DatabaseValidation
{
    /**
     * Validate table name for security.
     *
     * @param string $table
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateTable(string $table): void
    {
        if (empty($table)) {
            throw new InvalidArgumentException('Table name cannot be empty');
        }
        
        // بررسی الگوی امن برای نام جدول
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new InvalidArgumentException('Invalid table name format. Only letters, numbers and underscores allowed.');
        }

        // بررسی طول نام جدول
        if (strlen($table) > 64) {
            throw new InvalidArgumentException('Table name too long. Maximum 64 characters allowed.');
        }
    }

    /**
     * Validate fields array.
     *
     * @param array $fields
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateFields(array $fields): void
    {
        if (empty($fields)) {
            throw new InvalidArgumentException('Fields array cannot be empty');
        }
        
        foreach ($fields as $field) {
            if (!$field instanceof Field) {
                throw new InvalidArgumentException('All fields must be instances of Field class');
            }
        }
    }

    /**
     * Validate column name for security.
     *
     * @param string $column
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateColumn(string $column): void
    {
        if (empty($column)) {
            throw new InvalidArgumentException('Column name cannot be empty');
        }
        
        // اجازه table.column format، SQL functions، و کاراکترهای خاص مثل * در functions
        if (!preg_match('/^[a-zA-Z0-9_.()\\*\\s,`]+$/', $column)) {
            throw new InvalidArgumentException('Invalid column name format');
        }

        // بررسی طول نام ستون
        if (strlen($column) > 128) {
            throw new InvalidArgumentException('Column name too long. Maximum 128 characters allowed.');
        }

        // بررسی کلمات کلیدی خطرناک SQL (فقط در صورت standalone استفاده)
        $dangerousPatterns = [
            '/^DROP\s+/i', '/^DELETE\s+/i', '/^TRUNCATE\s+/i', 
            '/^ALTER\s+/i', '/^INSERT\s+/i', '/^UPDATE\s+/i'
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, trim($column))) {
                throw new InvalidArgumentException('Column name starts with dangerous SQL command');
            }
        }
    }

    /**
     * Validate SQL operator.
     *
     * @param string $operator
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateOperator(string $operator): void
    {
        $allowedOperators = [
            '=', '!=', '<>', '<', '>', '<=', '>=',
            'LIKE', 'NOT LIKE', 'ILIKE', 'NOT ILIKE',
            'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN',
            'IS NULL', 'IS NOT NULL', 'REGEXP', 'NOT REGEXP'
        ];
        
        if (!in_array(strtoupper($operator), $allowedOperators)) {
            throw new InvalidArgumentException('Invalid SQL operator: ' . $operator);
        }
    }

    /**
     * Validate sort direction.
     *
     * @param string $direction
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateSortDirection(string $direction): void
    {
        $direction = strtoupper(trim($direction));
        
        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new InvalidArgumentException('Sort direction must be ASC or DESC, got: ' . $direction);
        }
    }

    /**
     * Validate per page value.
     *
     * @param int $perPage
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validatePerPage(int $perPage): void
    {
        if ($perPage <= 0 || $perPage > 1000) {
            throw new InvalidArgumentException('Per page must be between 1 and 1000');
        }
    }

    /**
     * Validate filter object.
     *
     * @param Column $filter
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateFilter(Column $filter): void
    {
        $this->validateColumn($filter->column);
        $this->validateOperator($filter->operator);
        
        // اعتبارسنجی اضافی برای operatorهای خاص
        $upperOperator = strtoupper($filter->operator);
        
        if (in_array($upperOperator, ['IN', 'NOT IN'])) {
            if (!is_array($filter->value)) {
                throw new InvalidArgumentException('IN/NOT IN operators require array values');
            }
            
            if (empty($filter->value)) {
                throw new InvalidArgumentException('IN/NOT IN operators cannot have empty array values');
            }
        }
        
        if (in_array($upperOperator, ['BETWEEN', 'NOT BETWEEN'])) {
            if (!is_array($filter->value) || count($filter->value) !== 2) {
                throw new InvalidArgumentException('BETWEEN operators require exactly 2 values in array');
            }
        }
        
        if (in_array($upperOperator, ['IS NULL', 'IS NOT NULL'])) {
            if ($filter->value !== null) {
                throw new InvalidArgumentException('NULL check operators should not have values');
            }
        }
    }

    /**
     * Validate JOIN type.
     *
     * @param string $type
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateJoinType(string $type): void
    {
        $validTypes = ['inner', 'left', 'right', 'cross', 'full'];
        
        if (!in_array(strtolower(trim($type)), $validTypes)) {
            throw new InvalidArgumentException('Invalid JOIN type. Allowed: ' . implode(', ', $validTypes));
        }
    }

    /**
     * Validate pagination parameters.
     *
     * @param int $page
     * @param int $perPage
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validatePagination(int $page, int $perPage): void
    {
        if ($page <= 0) {
            throw new InvalidArgumentException('Page number must be greater than 0');
        }
        
        $this->validatePerPage($perPage);
        
        // محدودیت منطقی برای جلوگیری از بارگذاری صفحات بسیار بالا
        if ($page > 10000) {
            throw new InvalidArgumentException('Page number too high. Maximum 10000 allowed.');
        }
    }

    /**
     * Validate array of values for security.
     *
     * @param array $values
     * @param int $maxCount
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateArrayValues(array $values, int $maxCount = 1000): void
    {
        if (empty($values)) {
            throw new InvalidArgumentException('Values array cannot be empty');
        }
        
        if (count($values) > $maxCount) {
            throw new InvalidArgumentException("Too many values. Maximum {$maxCount} allowed.");
        }
        
        // بررسی وجود null values
        if (in_array(null, $values, true)) {
            throw new InvalidArgumentException('Null values are not allowed in array');
        }
    }
}
