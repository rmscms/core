<?php

namespace RMS\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Setting Model
 * 
 * Simple key-value storage for application settings with caching support
 */
class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Cache key prefix for settings
     */
    const CACHE_PREFIX = 'rms_setting_';
    
    /**
     * Cache duration in seconds (24 hours)
     */
    const CACHE_DURATION = 86400;

    /**
     * Boot method to handle cache clearing on model events
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when setting is created, updated, or deleted
        static::saved(function ($setting) {
            Cache::forget(self::CACHE_PREFIX . $setting->key);
            Cache::forget('rms_all_settings');
        });

        static::deleted(function ($setting) {
            Cache::forget(self::CACHE_PREFIX . $setting->key);
            Cache::forget('rms_all_settings');
        });
    }

    /**
     * Get a setting value by key with caching
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember(
            self::CACHE_PREFIX . $key,
            self::CACHE_DURATION,
            function () use ($key, $default) {
                $setting = static::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            }
        );
    }

    /**
     * Set a setting value by key
     *
     * @param string $key
     * @param mixed $value
     * @return Setting
     */
    public static function set(string $key, $value): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get all settings as array with caching
     *
     * @return array
     */
    public static function getAll(): array
    {
        return Cache::remember(
            'rms_all_settings',
            self::CACHE_DURATION,
            function () {
                return static::pluck('value', 'key')->toArray();
            }
        );
    }

    /**
     * Clear all settings cache
     *
     * @return void
     */
    public static function clearCache(): void
    {
        // Clear individual setting caches
        $keys = static::pluck('key');
        foreach ($keys as $key) {
            Cache::forget(self::CACHE_PREFIX . $key);
        }
        
        // Clear all settings cache
        Cache::forget('rms_all_settings');
    }

    /**
     * Check if a setting key exists
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Remove a setting by key
     *
     * @param string $key
     * @return bool
     */
    public static function remove(string $key): bool
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->delete() : false;
    }

    /**
     * Get multiple settings by keys
     *
     * @param array $keys
     * @return array
     */
    public static function getMany(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = static::get($key);
        }
        return $result;
    }

    /**
     * Set multiple settings at once
     *
     * @param array $settings
     * @return void
     */
    public static function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            static::set($key, $value);
        }
    }
}