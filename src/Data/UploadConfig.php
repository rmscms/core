<?php

namespace RMS\Core\Data;

/**
 * Upload Configuration Object with Fluent Methods
 * 
 * یک کلاس Object-oriented برای تنظیمات آپلود فایل که به جای استفاده از array های پیچیده،
 * متدهای fluent و قابل فهم ارائه می‌دهد.
 * 
 * @version 1.0.0
 * @author RMS Core Team
 */
class UploadConfig
{
    /**
     * تنظیمات آپلود
     */
    protected array $config = [
        'disk' => 'public',
        'path' => 'uploads',
        'types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'max_size' => 2048, // KB
        'multiple' => false,
        'use_model_id' => true,
        'ajax_upload' => false,
        'viewer_enabled' => true,
        'list_thumbnail_size' => [40, 40],
    ];

    /**
     * Create new upload configuration
     * 
     * @param string|null $fieldName نام فیلد (اختیاری)
     */
    public function __construct(?string $fieldName = null)
    {
        if ($fieldName) {
            $this->config['field_name'] = $fieldName;
        }
    }

    /**
     * Factory method برای شروع آسان
     * 
     * @param string|null $fieldName
     * @return static
     */
    public static function create(?string $fieldName = null): static
    {
        return new static($fieldName);
    }

    // === 🗄️ Storage Configuration ===

    /**
     * تنظیم disk ذخیره‌سازی
     * 
     * @param string $disk نام disk ('public', 'local', 's3', ...)
     * @return $this
     */
    public function disk(string $disk): static
    {
        $this->config['disk'] = $disk;
        return $this;
    }

    /**
     * تنظیم مسیر ذخیره‌سازی
     * 
     * @param string $path مسیر نسبی ('uploads/avatars', 'gallery', ...)
     * @return $this
     */
    public function path(string $path): static
    {
        $this->config['path'] = $path;
        return $this;
    }

    // === 📁 File Validation ===

    /**
     * تنظیم حداکثر اندازه فایل
     * 
     * @param int|string $size اندازه (2048 یا '2MB' یا '5MB')
     * @return $this
     */
    public function maxSize(int|string $size): static
    {
        if (is_string($size)) {
            // تبدیل '2MB' به KB
            if (preg_match('/^(\d+)\s*MB$/i', $size, $matches)) {
                $size = intval($matches[1]) * 1024;
            } elseif (preg_match('/^(\d+)\s*KB$/i', $size, $matches)) {
                $size = intval($matches[1]);
            }
        }
        
        $this->config['max_size'] = $size;
        return $this;
    }

    /**
     * تنظیم انواع فایل‌های مجاز
     * 
     * @param array|string $types آرایه یا رشته انواع فایل
     * @return $this
     */
    public function allowedTypes(array|string $types): static
    {
        if (is_string($types)) {
            $types = explode(',', $types);
            $types = array_map('trim', $types);
        }
        
        $this->config['types'] = $types;
        return $this;
    }

    // === 📷 Image-specific Methods ===

    /**
     * تنظیمات ویژه تصاویر
     * 
     * @return $this
     */
    public function forImages(): static
    {
        $this->config['types'] = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $this->config['max_size'] = 2048; // 2MB
        $this->config['viewer_enabled'] = true;
        return $this;
    }

    /**
     * تنظیمات ویژه اسناد
     * 
     * @return $this
     */
    public function forDocuments(): static
    {
        $this->config['types'] = ['pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx'];
        $this->config['max_size'] = 10240; // 10MB
        $this->config['viewer_enabled'] = false;
        return $this;
    }

    // === 🔧 Upload Behavior ===

    /**
     * فعال‌سازی آپلود چندتایی
     * 
     * @param bool $multiple
     * @return $this
     */
    public function multiple(bool $multiple = true): static
    {
        $this->config['multiple'] = $multiple;
        return $this;
    }

    /**
     * استفاده از ID مدل برای نام‌گذاری فایل
     * 
     * @param bool $useModelId
     * @return $this
     */
    public function useModelId(bool $useModelId = true): static
    {
        $this->config['use_model_id'] = $useModelId;
        return $this;
    }

    /**
     * فعال‌سازی آپلود AJAX
     * 
     * @param bool $ajaxUpload
     * @return $this
     */
    public function ajaxUpload(bool $ajaxUpload = true): static
    {
        $this->config['ajax_upload'] = $ajaxUpload;
        return $this;
    }

    // === 👀 Display Settings ===

    /**
     * فعال‌سازی image viewer
     * 
     * @param bool $enabled
     * @return $this
     */
    public function viewerEnabled(bool $enabled = true): static
    {
        $this->config['viewer_enabled'] = $enabled;
        return $this;
    }

    /**
     * تنظیم اندازه thumbnail در لیست
     * 
     * @param int $width
     * @param int $height
     * @return $this
     */
    public function listThumbnailSize(int $width, int $height): static
    {
        $this->config['list_thumbnail_size'] = [$width, $height];
        return $this;
    }

    // === 🖼️ Image Processing ===

    /**
     * تنظیمات تغییر اندازه خودکار
     * 
     * @param int $width
     * @param int $height
     * @param int $quality کیفیت (0-100)
     * @return $this
     */
    public function resize(int $width, int $height, int $quality = 85): static
    {
        $this->config['resize'] = [
            'width' => $width,
            'height' => $height,
            'quality' => $quality
        ];
        return $this;
    }

    /**
     * تنظیم thumbnails در اندازه‌های مختلف
     * 
     * @param array $thumbnails مثال: ['small' => [64, 64], 'medium' => [150, 150]]
     * @return $this
     */
    public function thumbnails(array $thumbnails): static
    {
        $this->config['thumbnails'] = $thumbnails;
        return $this;
    }

    // === ⚡ Quick Presets ===

    /**
     * پیش‌تنظیم برای آواتار کاربر
     * 
     * @return $this
     */
    public function avatar(): static
    {
        return $this->forImages()
            ->path('uploads/avatars')
            ->maxSize('2MB')
            ->multiple(false)
            ->useModelId(true)
            ->resize(300, 300)
            ->thumbnails([
                'small' => [64, 64],
                'medium' => [150, 150]
            ])
            ->listThumbnailSize(50, 50);
    }

    /**
     * پیش‌تنظیم برای گالری تصاویر
     * 
     * @return $this
     */
    public function gallery(): static
    {
        return $this->forImages()
            ->path('uploads/gallery')
            ->maxSize('5MB')
            ->multiple(true)
            ->useModelId(true)
            ->ajaxUpload(true)
            ->listThumbnailSize(60, 60);
    }

    /**
     * پیش‌تنظیم برای اسناد شخصی
     * 
     * @return $this
     */
    public function documents(): static
    {
        return $this->forDocuments()
            ->disk('local') // private storage
            ->path('documents/users')
            ->maxSize('10MB')
            ->multiple(false)
            ->useModelId(false); // نام‌های تصادفی برای امنیت
    }

    // === 🔧 Advanced Settings ===

    /**
     * اضافه کردن تنظیم سفارشی
     * 
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set(string $key, mixed $value): static
    {
        $this->config[$key] = $value;
        return $this;
    }

    /**
     * دریافت تنظیم خاص
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    // === 🔄 Convert to Array ===

    /**
     * تبدیل به array برای سازگاری با کد موجود
     * 
     * @return array
     */
    public function toArray(): array
    {
        return $this->config;
    }

    /**
     * Magic method برای تبدیل خودکار به array
     */
    public function __toArray(): array
    {
        return $this->toArray();
    }

    /**
     * JSON serialize
     * 
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * toString برای debug
     * 
     * @return string
     */
    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // === 🔍 Helper Methods ===

    /**
     * بررسی اینکه آپلود چندتایی است یا نه
     * 
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->config['multiple'] ?? false;
    }

    /**
     * بررسی اینکه AJAX upload فعال است یا نه
     * 
     * @return bool
     */
    public function isAjaxEnabled(): bool
    {
        return $this->config['ajax_upload'] ?? false;
    }

    /**
     * بررسی اینکه image viewer فعال است یا نه
     * 
     * @return bool
     */
    public function isViewerEnabled(): bool
    {
        return $this->config['viewer_enabled'] ?? false;
    }

    /**
     * دریافت حداکثر سایز به صورت خوانا
     * 
     * @return string
     */
    public function getMaxSizeFormatted(): string
    {
        $sizeKB = $this->config['max_size'] ?? 0;
        if ($sizeKB >= 1024) {
            return round($sizeKB / 1024, 1) . ' MB';
        }
        return $sizeKB . ' KB';
    }

    /**
     * دریافت لیست انواع فایل‌های مجاز به صورت رشته
     * 
     * @return string
     */
    public function getAllowedTypesString(): string
    {
        $types = $this->config['types'] ?? [];
        return strtoupper(implode(', ', $types));
    }
}