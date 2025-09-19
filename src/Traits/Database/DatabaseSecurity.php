<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Database;

use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

/**
 * Database security trait for managing security constraints and protections.
 * 
 * مدیریت محدودیت‌های امنیتی و حفاظت از پایگاه داده
 * 
 * @package RMS\Core\Traits\Database
 */
trait DatabaseSecurity
{
    /**
     * Security constraints that are always applied.
     */
    protected array $securityConstraints = [];

    /**
     * Maximum allowed query complexity score.
     */
    protected int $maxQueryComplexity = 100;

    /**
     * Current query complexity score.
     */
    protected int $currentComplexity = 0;

    /**
     * Apply security constraints to the query.
     * 
     * اعمال محدودیت‌های امنیتی به کوئری
     *
     * @return void
     */
    protected function applySecurityConstraints(): void
    {
        foreach ($this->securityConstraints as $constraint) {
            $this->sql->where(
                $constraint['column'], 
                $constraint['operator'], 
                $constraint['value']
            );
        }

        // لاگ کردن اعمال محدودیت‌های امنیتی
        if (!empty($this->securityConstraints)) {
            Log::info('Applied security constraints', [
                'table' => $this->table,
                'constraints_count' => count($this->securityConstraints)
            ]);
        }
    }

    /**
     * Add security constraint to always apply.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addSecurityConstraint(string $column, string $operator, mixed $value): self
    {
        $this->validateColumn($column);
        $this->validateOperator($operator);
        
        // بررسی عدم تکرار محدودیت
        $constraintKey = $column . $operator . serialize($value);
        
        foreach ($this->securityConstraints as $existingConstraint) {
            $existingKey = $existingConstraint['column'] . 
                          $existingConstraint['operator'] . 
                          serialize($existingConstraint['value']);
            
            if ($constraintKey === $existingKey) {
                // محدودیت تکراری است، نادیده می‌گیریم
                return $this;
            }
        }
        
        $this->securityConstraints[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'applied_at' => now()
        ];
        
        return $this;
    }

    /**
     * Remove a security constraint.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return $this
     */
    public function removeSecurityConstraint(string $column, string $operator, mixed $value): self
    {
        $constraintKey = $column . $operator . serialize($value);
        
        $this->securityConstraints = array_filter($this->securityConstraints, function($constraint) use ($constraintKey) {
            $existingKey = $constraint['column'] . 
                          $constraint['operator'] . 
                          serialize($constraint['value']);
            
            return $constraintKey !== $existingKey;
        });
        
        return $this;
    }

    /**
     * Clear all security constraints.
     *
     * @return $this
     */
    public function clearSecurityConstraints(): self
    {
        $this->securityConstraints = [];
        return $this;
    }

    /**
     * Get all applied security constraints.
     *
     * @return array
     */
    public function getSecurityConstraints(): array
    {
        return $this->securityConstraints;
    }

    /**
     * Add role-based security constraint.
     *
     * @param string $userRole
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function addRoleConstraint(string $userRole, string $column, mixed $value): self
    {
        // محدودیت بر اساس نقش کاربر
        $this->addSecurityConstraint($column, '=', $value);
        
        Log::info('Added role-based constraint', [
            'role' => $userRole,
            'column' => $column,
            'table' => $this->table
        ]);
        
        return $this;
    }

    /**
     * Add tenant-based security for multi-tenant applications.
     *
     * @param int|string $tenantId
     * @param string $tenantColumn
     * @return $this
     */
    public function addTenantConstraint(int|string $tenantId, string $tenantColumn = 'tenant_id'): self
    {
        $this->addSecurityConstraint($tenantColumn, '=', $tenantId);
        
        Log::info('Added tenant constraint', [
            'tenant_id' => $tenantId,
            'column' => $tenantColumn,
            'table' => $this->table
        ]);
        
        return $this;
    }

    /**
     * Add soft delete constraint (exclude deleted records).
     *
     * @param string $deletedColumn
     * @return $this
     */
    public function excludeDeleted(string $deletedColumn = 'deleted_at'): self
    {
        $this->addSecurityConstraint($deletedColumn, 'IS NULL', null);
        return $this;
    }

    /**
     * Add published content constraint.
     *
     * @param string $statusColumn
     * @param mixed $publishedValue
     * @return $this
     */
    public function onlyPublished(string $statusColumn = 'status', mixed $publishedValue = 'published'): self
    {
        $this->addSecurityConstraint($statusColumn, '=', $publishedValue);
        return $this;
    }

    /**
     * Add user ownership constraint.
     *
     * @param int|string $userId
     * @param string $userColumn
     * @return $this
     */
    public function ownedByUser(int|string $userId, string $userColumn = 'user_id'): self
    {
        $this->addSecurityConstraint($userColumn, '=', $userId);
        return $this;
    }

    /**
     * Increase query complexity score.
     *
     * @param int $points
     * @return void
     * @throws InvalidArgumentException
     */
    protected function increaseComplexity(int $points): void
    {
        $this->currentComplexity += $points;
        
        if ($this->currentComplexity > $this->maxQueryComplexity) {
            throw new InvalidArgumentException('Query complexity exceeds maximum allowed limit');
        }
    }

    /**
     * Set maximum query complexity.
     *
     * @param int $maxComplexity
     * @return $this
     */
    public function setMaxComplexity(int $maxComplexity): self
    {
        $this->maxQueryComplexity = max(1, $maxComplexity);
        return $this;
    }

    /**
     * Get current query complexity score.
     *
     * @return int
     */
    public function getComplexity(): int
    {
        return $this->currentComplexity;
    }

    /**
     * Reset query complexity score.
     *
     * @return $this
     */
    public function resetComplexity(): self
    {
        $this->currentComplexity = 0;
        return $this;
    }
}
