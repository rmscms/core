<?php

namespace RMS\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Bug Log Model for tracking errors and fixes
 * 
 * مدل لاگ خطاها برای پیگیری خطاها و فیکس‌ها
 * 
 * @property int $id
 * @property string $title
 * @property string $error_message
 * @property string|null $error_code
 * @property string|null $file_path
 * @property int|null $line_number
 * @property string|null $request_url
 * @property string|null $request_method
 * @property int|null $user_id
 * @property string|null $session_id
 * @property string|null $user_agent
 * @property string|null $ip_address
 * @property string|null $stack_trace
 * @property array|null $request_data
 * @property array|null $debug_info
 * @property string $severity
 * @property string|null $category
 * @property string $status
 * @property bool $ai_fixed
 * @property string|null $ai_fix_description
 * @property array|null $ai_fix_files
 * @property bool $human_confirmed
 * @property string|null $human_confirmation_notes
 * @property \Carbon\Carbon $occurred_at
 * @property \Carbon\Carbon|null $ai_fixed_at
 * @property \Carbon\Carbon|null $human_confirmed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class BugLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'bug_logs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title', 'error_message', 'error_code', 'file_path', 'line_number',
        'request_url', 'request_method', 'user_id', 'session_id', 
        'user_agent', 'ip_address', 'stack_trace', 'request_data', 
        'debug_info', 'severity', 'category', 'status', 
        'ai_fixed', 'ai_fix_description', 'ai_fix_files',
        'human_confirmed', 'human_confirmation_notes', 'occurred_at',
        'ai_fixed_at', 'human_confirmed_at'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'request_data' => 'array',
        'debug_info' => 'array', 
        'ai_fix_files' => 'array',
        'ai_fixed' => 'boolean',
        'human_confirmed' => 'boolean',
        'occurred_at' => 'datetime',
        'ai_fixed_at' => 'datetime',
        'human_confirmed_at' => 'datetime'
    ];

    /**
     * Severity constants
     */
    const SEVERITY_LOW = 'LOW';
    const SEVERITY_MEDIUM = 'MEDIUM';
    const SEVERITY_HIGH = 'HIGH';
    const SEVERITY_CRITICAL = 'CRITICAL';

    /**
     * Status constants
     */
    const STATUS_NEW = 'NEW';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_FIXED = 'FIXED';
    const STATUS_CONFIRMED = 'CONFIRMED';
    const STATUS_CLOSED = 'CLOSED';

    /**
     * Category constants
     */
    const CATEGORY_FORM = 'Form';
    const CATEGORY_DATABASE = 'Database';
    const CATEGORY_AUTH = 'Authentication';
    const CATEGORY_VALIDATION = 'Validation';
    const CATEGORY_CONTROLLER = 'Controller';
    const CATEGORY_VIEW = 'View';
    const CATEGORY_GENERAL = 'General';

    // ================================
    // Scopes
    // ================================

    /**
     * Scope for new bugs
     */
    public function scopeNew($query) 
    {
        return $query->where('status', self::STATUS_NEW);
    }

    /**
     * Scope for AI fixed bugs (waiting for human confirmation)
     */
    public function scopeFixed($query)
    {
        return $query->where('ai_fixed', true)->where('human_confirmed', false);
    }

    /**
     * Scope for human confirmed bugs
     */
    public function scopeConfirmed($query)
    {
        return $query->where('human_confirmed', true);
    }

    /**
     * Scope for bugs by severity
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope for bugs by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for critical bugs
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', self::SEVERITY_CRITICAL);
    }

    /**
     * Scope for recent bugs
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('occurred_at', '>=', now()->subDays($days));
    }

    // ================================
    // Helper Methods
    // ================================

    /**
     * Check if bug is fixed by AI
     */
    public function isFixed(): bool
    {
        return $this->ai_fixed;
    }

    /**
     * Check if bug is confirmed by human
     */
    public function isConfirmed(): bool
    {
        return $this->human_confirmed;
    }

    /**
     * Check if bug needs human confirmation
     */
    public function needsConfirmation(): bool
    {
        return $this->ai_fixed && !$this->human_confirmed;
    }

    /**
     * Check if bug is critical
     */
    public function isCritical(): bool
    {
        return $this->severity === self::SEVERITY_CRITICAL;
    }

    /**
     * Get severity badge color
     */
    public function getSeverityColor(): string
    {
        return match($this->severity) {
            self::SEVERITY_CRITICAL => 'danger',
            self::SEVERITY_HIGH => 'warning', 
            self::SEVERITY_MEDIUM => 'info',
            self::SEVERITY_LOW => 'success',
            default => 'secondary'
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_NEW => 'danger',
            self::STATUS_IN_PROGRESS => 'warning',
            self::STATUS_FIXED => 'info', 
            self::STATUS_CONFIRMED => 'success',
            self::STATUS_CLOSED => 'secondary',
            default => 'light'
        };
    }

    /**
     * Get short file path (last 50 characters)
     */
    public function getShortFilePath(): string
    {
        if (!$this->file_path) return '';
        
        return strlen($this->file_path) > 50 
            ? '...' . substr($this->file_path, -47)
            : $this->file_path;
    }

    /**
     * Get formatted error location
     */
    public function getErrorLocation(): string
    {
        if (!$this->file_path) return 'نامشخص';
        
        $location = $this->getShortFilePath();
        
        if ($this->line_number) {
            $location .= ':' . $this->line_number;
        }
        
        return $location;
    }

    /**
     * Mark as fixed by AI
     */
    public function markAsFixed(string $description, array $files = []): bool
    {
        return $this->update([
            'status' => self::STATUS_FIXED,
            'ai_fixed' => true,
            'ai_fix_description' => $description,
            'ai_fix_files' => $files,
            'ai_fixed_at' => now()
        ]);
    }

    /**
     * Confirm fix by human
     */
    public function confirmFix(string $notes = ''): bool
    {
        return $this->update([
            'status' => self::STATUS_CONFIRMED,
            'human_confirmed' => true,
            'human_confirmation_notes' => $notes,
            'human_confirmed_at' => now()
        ]);
    }

    /**
     * Reject fix by human
     */
    public function rejectFix(string $reason = ''): bool
    {
        return $this->update([
            'status' => self::STATUS_NEW,
            'ai_fixed' => false,
            'ai_fix_description' => null,
            'ai_fix_files' => null,
            'ai_fixed_at' => null,
            'human_confirmation_notes' => $reason ? "Fix rejected: $reason" : 'Fix rejected'
        ]);
    }

    /**
     * Close bug
     */
    public function close(): bool
    {
        return $this->update([
            'status' => self::STATUS_CLOSED
        ]);
    }
}
