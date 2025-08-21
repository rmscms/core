<?php

namespace RMS\Core\Enums;

use RMS\Core\Utils\FilterOptions;

class Role
{
    const SUPER_ADMIN = 'super_admin';
    const EDITOR = 'editor';
    const MODERATOR = 'moderator';

    public static function getFilterRoles(): array
    {
        return FilterOptions::make([
            ['id' => self::SUPER_ADMIN, 'name' => 'super_admin'],
            ['id' => self::EDITOR, 'name' => 'editor'],
            ['id' => self::MODERATOR, 'name' => 'moderator'],
        ]);
    }
}
