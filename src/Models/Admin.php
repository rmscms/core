<?php

namespace RMS\Core\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'role',
        'active',
        'last_login_at',
        'last_login_ip',
        'two_factor_enabled',
        'two_factor_secret',
        'remember_token',
    ];

    public function getTable()
    {
        return 'admins';
    }
}
