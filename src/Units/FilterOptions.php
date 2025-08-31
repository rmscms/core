<?php

namespace RMS\Core\Utils;

/**
 * Utility class for formatting filter options.
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
}
