<?php

namespace RMS\Core\Traits\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use RMS\Core\View\HelperList\Generator;
use RMS\Core\Data\UploadConfig;

/**
 * UploadFileControllerHelper Trait
 *
 * برای استفاده مجدد در کنترلرهای مختلف که نیاز به آپلود فایل دارند
 * حاوی متدهای afterAdd, afterUpdate, afterDestroy, beforeSendToTemplate
 *
 * @version 1.0.0
 */
trait UploadFileControllerHelper
{
    /**
     * بعد از ایجاد رکورد جدید - پردازش فایل‌های آپلود شده
     */
    protected function afterAdd(Request $request, $id, Model $model): void
    {
        // 📁 پردازش فایل‌های آپلود شده
        $uploadedFiles = $this->processFileUploads($request, $id);

        // 🔄 Update model with uploaded file paths
        if (!empty($uploadedFiles)) {
            foreach ($uploadedFiles as $fieldName => $filePath) {
                $model->{$fieldName} = $filePath;
            }
            $model->save();
        }

        // فراخوانی parent در صورت وجود
        if (method_exists(parent::class, 'afterAdd')) {
            parent::afterAdd($request, $id, $model);
        }
    }

    /**
     * بعد از به‌روزرسانی رکورد - پردازش فایل‌های جدید
     */
    protected function afterUpdate(Request $request, $id, Model $model): void
    {
        // 📁 پردازش فایل‌های آپلود شده
        $uploadedFiles = $this->processFileUploads($request, $id);

        // 🔄 Update model with uploaded file paths
        if (!empty($uploadedFiles)) {
            foreach ($uploadedFiles as $fieldName => $filePath) {
                $model->{$fieldName} = $filePath;
            }
            $model->save();
        }

        // فراخوانی parent در صورت وجود
        if (method_exists(parent::class, 'afterUpdate')) {
            parent::afterUpdate($request, $id, $model);
        }
    }

    /**
     * بعد از حذف رکورد - پاک‌سازی فایل‌های مرتبط
     */
    protected function afterDestroy(int|string $id): void
    {
        // حذف فایل‌های مربوط به این مدل
        $this->cleanupModelFiles($id);

        // فراخوانی parent در صورت وجود
        if (method_exists(parent::class, 'afterDestroy')) {
            parent::afterDestroy($id);
        }
    }

    /**
     * قبل از ارسال داده به template - فیلتر فیلدهای AJAX و تنظیم داده‌های موجود
     */
    protected function beforeSendToTemplate(array &$templateData, $generated): void
    {
        // فراخوانی parent اجباری
        parent::beforeSendToTemplate($templateData, $generated);

        // دریافت ID مدل
        $modelId = $generated->getGenerator()->getId();

        // تشخیص حالت create/edit
        $isCreateMode = !$modelId;

        // حذف فیلدهای AJAX از حالت create
        $this->filterAjaxUploadFields($templateData, $isCreateMode);

        // تنظیم داده‌های فایل موجود برای فیلدهای تصویری
        foreach ($templateData['fields'] as $field) {
            if ($field->type === \RMS\Core\Data\Field::IMAGE) {
                $this->setExistingFileData($field, $modelId, $templateData['model'] ?? null);
            }
        }


    }

    // ======= NEW IMAGE VIEWER METHODS =======

    /**
     * رندر کردن فیلد تصویری برای نمایش در لیست
     *
     * @param object $item آیتم مدل
     * @param string $fieldName نام فیلد
     * @return string
     */
    public function renderImageField($item, string $fieldName): string
    {
        $uploadConfig = $this->getNormalizedUploadConfig();
        $fieldConfig = $uploadConfig[$fieldName] ?? [];

        // اگر viewer غیرفعال باشد
        if (!($fieldConfig['viewer_enabled'] ?? false)) {
            return $this->renderSimpleImage($item, $fieldName, $fieldConfig);
        }

        // بررسی وجود فایل
        $fieldValue = $item->{$fieldName} ?? null;
        if (empty($fieldValue)) {
            return $this->renderDefaultImageIcon($fieldConfig);
        }

        // اندازه thumbnail
        $thumbnailSize = $fieldConfig['list_thumbnail_size'] ?? [40, 40];
        [$width, $height] = $thumbnailSize;

        // آدرس فایل
        $imageUrl = $this->getImageUrl($fieldValue, $fieldConfig);

        // آدرس endpoint داینامیک
        $viewerEndpoint = $this->getImageViewerEndpoint($item->id, $fieldName);

        // HTML برای نمایش thumbnail با قابلیت کلیک
        return '<div class="image-thumbnail-wrapper">' .
            '<img src="' . $imageUrl . '" ' .
            'class="rounded image-thumbnail cursor-pointer" ' .
            'style="width: ' . $width . 'px; height: ' . $height . 'px; object-fit: cover;" ' .
            'data-item-id="' . $item->id . '" ' .
            'data-field-name="' . $fieldName . '" ' .
            'data-viewer-endpoint="' . $viewerEndpoint . '" ' .
            'title="کلیک برای نمایش اندازه اصلی" ' .
            'onclick="showImageViewer(this)"/>' .
            '</div>';
    }

    /**
     * تولید آدرس endpoint برای image viewer
     *
     * @param int|string $id شناسه آیتم
     * @param string $fieldName نام فیلد
     * @return string
     */
    public function getImageViewerEndpoint($id, string $fieldName): string
    {
        $routeParameter = $this->routeParameter();
        $baseRoute = $this->baseRoute();

        return route("admin.{$baseRoute}.image-viewer", [
            $routeParameter => $id,
            'field' => $fieldName
        ]);
    }

    /**
     * مدیریت درخواست AJAX برای image viewer
     *
     * @param Request $request
     * @param int|string $id
     * @param string $fieldName
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleImageViewer(Request $request, $id, string $fieldName)
    {
        $model = $this->model()->find($id);

        if (!$model) {
            return response()->json([
                'success' => false,
                'message' => 'آیتم یافت نشد'
            ], 404);
        }

        $fieldValue = $model->{$fieldName} ?? null;
        if (empty($fieldValue)) {
            return response()->json([
                'success' => false,
                'message' => 'تصویر یافت نشد'
            ], 404);
        }

        $uploadConfig = $this->getNormalizedUploadConfig();
        $fieldConfig = $uploadConfig[$fieldName] ?? [];

        $imageUrl = $this->getImageUrl($fieldValue, $fieldConfig);

        return response()->json([
            'success' => true,
            'data' => [
                'item_id' => $model->id,
                'field_name' => $fieldName,
                'image_url' => $imageUrl,
                'filename' => $fieldValue
            ]
        ]);
    }

    // ======= HELPER METHODS =======

    /**
     * تنظیم خودکار image viewer برای فیلدهای IMAGE
     */
    protected function setupImageViewerFields( &$templateData): void
    {
        if (!method_exists($this, 'getUploadConfig')) {
            return;
        }
        $uploadConfig = $this->getNormalizedUploadConfig();
        foreach ($templateData->fields as &$field) {
            if ($field->type === \RMS\Core\Data\Field::IMAGE) {
                $fieldConfig = $uploadConfig[$field->key] ?? [];

                // اگر viewer فعال باشد، customMethod رو از پیش نداشته باشد
                if (($fieldConfig['viewer_enabled'] ?? false) && empty($field->customMethod)) {
                    $field->customMethod('renderImageField');
                }
            }
        }
    }
    protected function beforeSendToListTemplate(&$listResponse): void
    {

        // ✅ Auto-setup image viewer for IMAGE fields
        $this->setupImageViewerFields($listResponse);
    }
    /**
     * رندر ساده تصویر بدون viewer
     */
    protected function renderSimpleImage($item, string $fieldName, array $fieldConfig): string
    {
        $fieldValue = $item->{$fieldName} ?? null;
        if (empty($fieldValue)) {
            return $this->renderDefaultImageIcon($fieldConfig);
        }

        $thumbnailSize = $fieldConfig['list_thumbnail_size'] ?? [40, 40];
        [$width, $height] = $thumbnailSize;

        $imageUrl = $this->getImageUrl($fieldValue, $fieldConfig);

        return '<img src="' . $imageUrl . '" class="rounded" ' .
            'style="width: ' . $width . 'px; height: ' . $height . 'px; object-fit: cover;" />';
    }

    /**
     * نمایش آیکون پیش‌فرض برای عدم وجود تصویر
     */
    protected function renderDefaultImageIcon(array $fieldConfig): string
    {
        $thumbnailSize = $fieldConfig['list_thumbnail_size'] ?? [40, 40];
        [$width, $height] = $thumbnailSize;

        return '<div class="d-flex align-items-center justify-content-center bg-light rounded" ' .
            'style="width: ' . $width . 'px; height: ' . $height . 'px;">' .
            '<i class="ph-image text-muted"></i>' .
            '</div>';
    }

    /**
     * تولید URL تصویر
     */
    protected function getImageUrl($fieldValue, array $fieldConfig): string
    {
        $disk = $fieldConfig['disk'] ?? 'public';
        $path = $fieldConfig['path'] ?? 'uploads';

        if ($disk === 'public') {
            return asset('storage/' . $fieldValue);
        }

        // برای private disks باید route جداگانه برای serve فایل داشته باشیم
        return route('file.serve', ['path' =>   $fieldValue]);
    }

    /**
     * Normalize upload configuration to array
     * تبدیل UploadConfig Object به array برای سازگاری با کد موجود
     * 
     * @param mixed $config
     * @return array
     */
    protected function normalizeUploadConfig($config): array
    {
        if ($config instanceof UploadConfig) {
            return $config->toArray();
        }
        
        return (array) $config;
    }

    /**
     * دریافت تنظیمات آپلود به صورت normalized
     * 
     * @return array
     */
    protected function getNormalizedUploadConfig(): array
    {
        if (!method_exists($this, 'getUploadConfig')) {
            return [];
        }
        
        $config = $this->getUploadConfig();
        $normalizedConfig = [];
        
        foreach ($config as $fieldName => $fieldConfig) {
            $normalizedConfig[$fieldName] = $this->normalizeUploadConfig($fieldConfig);
        }
        
        return $normalizedConfig;
    }
}
