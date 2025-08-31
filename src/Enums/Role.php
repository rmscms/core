<?php

namespace RMS\Core\Enums;

use RMS\Core\Utils\FilterOptions;

/**
 * Enum for admin roles.
 */
enum Role: string
{
    case SUPER_ADMIN = 'super_admin';
    case EDITOR = 'editor';
    case MODERATOR = 'moderator';

    /**
     * Get roles for filter dropdowns.
     *
     * @return array
     */
    public static function getFilterRoles(): array
    {
        return FilterOptions::make([
            ['id' => self::SUPER_ADMIN->value, 'name' => 'super_admin'],
            ['id' => self::EDITOR->value, 'name' => 'editor'],
            ['id' => self::MODERATOR->value, 'name' => 'moderator'],
        ]);
    }
}
