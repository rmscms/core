<?php

namespace RMS\Core\Utils;

class FilterOptions
{
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
