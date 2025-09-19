<?php

namespace RMS\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'group_id',
        'active',
        'email_notifications',
        'birth_date',
        'avatar',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
        'email_notifications' => 'boolean',
        'birth_date' => 'date',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Check if user is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active === true;
    }

    /**
     * Get the user's avatar URL.
     *
     * @param int $size
     * @return string
     */
    public function getAvatarUrl(int $size = 64): string
    {
        if ($this->avatar && file_exists(public_path($this->avatar))) {
            return asset($this->avatar);
        }
        
        // Fallback to Gravatar
        $email = strtolower(trim($this->email));
        $hash = md5($email);
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=identicon";
    }

    /**
     * Scope to get only active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get users by group.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $groupId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByGroup($query, int $groupId)
    {
        return $query->where('group_id', $groupId);
    }

    /**
     * Get table name.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }
}