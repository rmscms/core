<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Upload;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RMS\Core\Debug\RMSDebugger;
use RMS\Core\Data\UploadConfig;

/**
 * HasFileUpload Trait
 *
 * Professional file upload management for RMS Core controllers
 * Supports both public and storage paths, multiple file types,
 * automatic validation, old file cleanup, and unique naming.
 *
 * @version 1.0.0
 * @author RMS Core Team
 */
trait HasFileUpload
{
    // Note: getUploadConfig() method must be implemented via HasUploadConfig interface

    /**
     * Handle file upload for a specific field with smart path management.
     *
     * @param string $fieldName Field name (e.g., 'avatar')
     * @param UploadedFile $file Uploaded file
     * @param int|string|null $modelId Model ID for smart naming
     * @param array|null $config Optional override config
     * @return string|null File path on success, null on failure
     */
    protected function handleFileUpload(string $fieldName, UploadedFile $file, int|string|null $modelId = null, ?array $config = null): ?string
    {
        $config = $config ?? $this->getFieldUploadConfig($fieldName);

        if (empty($config)) {
            $this->logUploadError("No upload configuration found for field: {$fieldName}");
            return null;
        }

        // Validate file
        if (!$this->validateUploadedFile($file, $config)) {
            return null;
        }

        try {
            // Build smart path
            $basePath = rtrim($config['path'] ?? 'uploads', '/');
            $disk = $config['disk'] ?? 'public';

            // Create model-specific folder for multiple files
            if (($config['multiple'] ?? false) && ($config['use_model_id'] ?? false) && $modelId) {
                $basePath = $basePath . '/' . $modelId;

                // Create directory if it doesn't exist
                if (!Storage::disk($disk)->exists($basePath)) {
                    Storage::disk($disk)->makeDirectory($basePath);
                    $this->logUploadSuccess("Created model directory", [
                        'field' => $fieldName,
                        'model_id' => $modelId,
                        'path' => $basePath,
                        'disk' => $disk
                    ]);
                }
            }

            // Generate filename
            $filename = $this->generateFileName($file, $config, $modelId);

            // Handle single file replacement (delete old file with same model ID)
            if (!($config['multiple'] ?? false) && ($config['use_model_id'] ?? false) && $modelId) {
                $this->deleteOldModelFile($fieldName, $basePath, $modelId, $disk);
            }

            // Store file
            $storedPath = $file->storeAs($basePath, $filename, $disk);

            if ($storedPath) {
                $this->logUploadSuccess("File uploaded successfully", [
                    'field' => $fieldName,
                    'model_id' => $modelId,
                    'filename' => $filename,
                    'path' => $storedPath,
                    'disk' => $disk,
                    'size' => $file->getSize(),
                    'multiple' => $config['multiple'] ?? false
                ]);

                return $storedPath;
            }
        } catch (\Exception $e) {
            $this->logUploadError("File upload failed: " . $e->getMessage(), [
                'field' => $fieldName,
                'model_id' => $modelId,
                'file' => $file->getClientOriginalName()
            ]);
        }

        return null;
    }

    /**
     * Delete old file when updating.
     *
     * @param string $fieldName Field name
     * @param string $oldPath Old file path
     * @return bool Success status
     */
    protected function deleteOldFile(string $fieldName, string $oldPath): bool
    {
        if (empty($oldPath)) {
            return true;
        }

        $config = $this->getFieldUploadConfig($fieldName);
        $disk = $config['disk'] ?? 'public';

        try {
            if (Storage::disk($disk)->exists($oldPath)) {
                $deleted = Storage::disk($disk)->delete($oldPath);

                if ($deleted) {
                    $this->logUploadSuccess("Old file deleted successfully", [
                        'field' => $fieldName,
                        'path' => $oldPath,
                        'disk' => $disk
                    ]);
                }

                return $deleted;
            }
        } catch (\Exception $e) {
            $this->logUploadError("Failed to delete old file: " . $e->getMessage(), [
                'field' => $fieldName,
                'path' => $oldPath
            ]);
        }

        return false;
    }

    /**
     * Delete old model file (for single file replacement).
     *
     * @param string $fieldName Field name
     * @param string $basePath Base upload path
     * @param int|string $modelId Model ID
     * @param string $disk Storage disk
     * @return bool Success status
     */
    protected function deleteOldModelFile(string $fieldName, string $basePath, int|string $modelId, string $disk): bool
    {
        try {
            // Find existing file with model ID pattern
            $files = Storage::disk($disk)->files($basePath);
            $pattern = "/^" . preg_quote((string)$modelId, '/') . "\.[a-zA-Z0-9]+$/";

            foreach ($files as $file) {
                $filename = basename($file);
                if (preg_match($pattern, $filename)) {
                    $deleted = Storage::disk($disk)->delete($file);

                    if ($deleted) {
                        $this->logUploadSuccess("Old model file replaced", [
                            'field' => $fieldName,
                            'model_id' => $modelId,
                            'old_file' => $file,
                            'disk' => $disk
                        ]);
                    }

                    return $deleted;
                }
            }
        } catch (\Exception $e) {
            $this->logUploadError("Failed to delete old model file: " . $e->getMessage(), [
                'field' => $fieldName,
                'model_id' => $modelId,
                'base_path' => $basePath
            ]);
        }

        return false;
    }

    /**
     * Delete entire model folder (for multiple files).
     *
     * @param string $fieldName Field name
     * @param int|string $modelId Model ID
     * @return bool Success status
     */
    protected function deleteModelFolder(string $fieldName, int|string $modelId): bool
    {
        $config = $this->getFieldUploadConfig($fieldName);

        if (!($config['multiple'] ?? false) || !($config['use_model_id'] ?? false)) {
            return true; // Not applicable for single files
        }

        $basePath = rtrim($config['path'] ?? 'uploads', '/');
        $modelPath = $basePath . '/' . $modelId;
        $disk = $config['disk'] ?? 'public';

        try {
            if (Storage::disk($disk)->exists($modelPath)) {
                $deleted = Storage::disk($disk)->deleteDirectory($modelPath);

                if ($deleted) {
                    $this->logUploadSuccess("Model folder deleted successfully", [
                        'field' => $fieldName,
                        'model_id' => $modelId,
                        'path' => $modelPath,
                        'disk' => $disk
                    ]);
                }

                return $deleted;
            }
        } catch (\Exception $e) {
            $this->logUploadError("Failed to delete model folder: " . $e->getMessage(), [
                'field' => $fieldName,
                'model_id' => $modelId,
                'path' => $modelPath
            ]);
        }

        return false;
    }

    /**
     * Get upload configuration for a specific field.
     *
     * @param string $fieldName
     * @return array
     */
    protected function getFieldUploadConfig(string $fieldName): array
    {
        $allConfig = $this->getNormalizedUploadConfig();
        return $allConfig[$fieldName] ?? [];
    }

    /**
     * Validate uploaded file.
     *
     * @param UploadedFile $file
     * @param array $config
     * @return bool
     */
    protected function validateUploadedFile(UploadedFile $file, array $config): bool
    {
        // Check if file is valid
        if (!$file->isValid()) {
            $this->logUploadError("Invalid file upload");
            return false;
        }

        // Check file size
        $maxSize = ($config['max_size'] ?? 2048) * 1024; // Convert KB to bytes
        if ($file->getSize() > $maxSize) {
            $this->logUploadError("File size exceeds maximum allowed", [
                'size' => $file->getSize(),
                'max_size' => $maxSize,
                'filename' => $file->getClientOriginalName()
            ]);
            return false;
        }

        // Check file extension
        $allowedTypes = $config['types'] ?? [];
        if (!empty($allowedTypes)) {
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $allowedTypes)) {
                $this->logUploadError("File type not allowed", [
                    'extension' => $extension,
                    'allowed' => $allowedTypes,
                    'filename' => $file->getClientOriginalName()
                ]);
                return false;
            }
        }

        // Check image dimensions if specified
        if (isset($config['dimensions']) && $this->isImageFile($file)) {
            $dimensions = getimagesize($file->path());
            if ($dimensions) {
                [$width, $height] = $dimensions;
                $maxWidth = $config['dimensions']['width'] ?? null;
                $maxHeight = $config['dimensions']['height'] ?? null;

                if (($maxWidth && $width > $maxWidth) || ($maxHeight && $height > $maxHeight)) {
                    $this->logUploadError("Image dimensions exceed maximum allowed", [
                        'current' => "{$width}x{$height}",
                        'max' => "{$maxWidth}x{$maxHeight}",
                        'filename' => $file->getClientOriginalName()
                    ]);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Generate smart filename based on configuration.
     *
     * @param UploadedFile $file
     * @param array $config
     * @param int|string|null $modelId
     * @return string
     */
    protected function generateFileName(UploadedFile $file, array $config, int|string|null $modelId = null): string
    {
        $extension = $file->getClientOriginalExtension();

        // Smart naming based on configuration
        if ($config['use_model_id'] ?? false && $modelId) {
            if ($config['multiple'] ?? false) {
                // Multiple files: use original name (will be in model-specific folder)
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $originalName = Str::slug($originalName);
                $timestamp = time();
                $random = Str::random(4); // Shorter random for multiple files

                return "{$timestamp}_{$random}_{$originalName}.{$extension}";
            } else {
                // Single file: use model ID as filename
                return "{$modelId}.{$extension}";
            }
        }

        // Fallback to unique naming
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $originalName = Str::slug($originalName);
        $timestamp = time();
        $random = Str::random(8);

        return "{$timestamp}_{$random}_{$originalName}.{$extension}";
    }

    /**
     * Check if file is an image.
     *
     * @param UploadedFile $file
     * @return bool
     */
    protected function isImageFile(UploadedFile $file): bool
    {
        return str_starts_with($file->getMimeType(), 'image/');
    }

    /**
     * Get full URL for file path.
     *
     * @param string $path File path
     * @param string $disk Disk name
     * @return string|null
     */
    protected function getFileUrl(string $path, string $disk = 'public'): ?string
    {
        if (empty($path)) {
            return null;
        }

        try {
            if ($disk === 'public') {
                return Storage::disk('public')->url($path);
            } else {
                // For private files, you might want to create a route for serving files
                // return route('admin.files.serve', ['path' => base64_encode($path)]);
                return null;
            }
        } catch (\Exception $e) {
            $this->logUploadError("Failed to generate file URL: " . $e->getMessage(), [
                'path' => $path,
                'disk' => $disk
            ]);
            return null;
        }
    }

    /**
     * Process all file uploads in request with smart model-based handling.
     * Supports both normal and AJAX upload modes.
     *
     * @param Request $request
     * @param int|string|null $id Record ID for updates
     * @param bool $onlyNormalUploads Only process non-AJAX fields
     * @return array Array of uploaded file paths
     */
    protected function processFileUploads(Request &$request, int|string|null $id = null, bool $onlyNormalUploads = true): array
    {
        $uploadedFiles = [];
        $uploadConfig = $this->getNormalizedUploadConfig();

        foreach ($uploadConfig as $fieldName => $config) {
            // Skip AJAX fields in normal processing (they're handled separately)
            if ($onlyNormalUploads && ($config['ajax_upload'] ?? false)) {
                $this->logUploadSuccess("Skipping AJAX upload field in normal processing", [
                    'field' => $fieldName,
                    'mode' => $id ? 'update' : 'create'
                ]);
                continue;
            }

            // Skip normal fields when processing AJAX uploads
            if (!$onlyNormalUploads && !($config['ajax_upload'] ?? false)) {
                continue;
            }

            if ($request->hasFile($fieldName)) {
                $files = $request->file($fieldName);

                // Handle both single and multiple files
                if (!is_array($files)) {
                    $files = [$files];
                }

                $uploadedPaths = [];

                foreach ($files as $file) {
                    // Upload file with model ID for smart naming
                    $uploadedPath = $this->handleFileUpload($fieldName, $file, $id, $config);

                    if ($uploadedPath) {
                        $uploadedPaths[] = $uploadedPath;
                    }
                }

                if (!empty($uploadedPaths)) {
                    // For single files, store path directly; for multiple, store as array
                    if ($config['multiple'] ?? false) {
                        $uploadedFiles[$fieldName] = $uploadedPaths;
                        // For multiple files, you might want to store as JSON in database
                        $request->merge([$fieldName => json_encode($uploadedPaths)]);
                    } else {
                        $uploadedFiles[$fieldName] = $uploadedPaths[0];
                        $request->merge([$fieldName => $uploadedPaths[0]]);
                    }
                } else {
                    // Remove field from request if upload failed
                    $request->request->remove($fieldName);
                }
            }
        }

        return $uploadedFiles;
    }

    /**
     * Process AJAX file uploads separately.
     *
     * @param Request $request
     * @param int|string $id Record ID (required for AJAX uploads)
     * @return array Array of uploaded file paths
     */
    protected function processAjaxFileUploads(Request $request, int|string $id): array
    {
        return $this->processFileUploads($request, $id, false);
    }

    /**
     * Log upload success.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logUploadSuccess(string $message, array $context = []): void
    {
        if (method_exists($this, 'debugLog')) {
            $this->debugLog('info', 'file_upload', $message, $context);
        } else {
            \Log::info("[File Upload] {$message}", $context);
        }
    }

    /**
     * Log upload error.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logUploadError(string $message, array $context = []): void
    {
        if (method_exists($this, 'debugLog')) {
            $this->debugLog('error', 'file_upload', $message, $context);
        } else {
            \Log::error("[File Upload] {$message}", $context);
        }
    }

    /**
     * Get file info for template display.
     *
     * @param string $fieldName
     * @param string|null $path
     * @return array|null
     */
    protected function getFileInfoForTemplate(string $fieldName, ?string $path): ?array
    {
        if (empty($path)) {
            return null;
        }

        $config = $this->getFieldUploadConfig($fieldName);
        $disk = $config['disk'] ?? 'public';

        try {
            if (Storage::disk($disk)->exists($path)) {
                return [
                    'path' => $path,
                    'url' => $this->getFileUrl($path, $disk),
                    'name' => basename($path),
                    'size' => Storage::disk($disk)->size($path),
                    'formatted_size' => $this->formatBytes(Storage::disk($disk)->size($path)),
                    'disk' => $disk,
                    'exists' => true
                ];
            }
        } catch (\Exception $e) {
            $this->logUploadError("Failed to get file info: " . $e->getMessage(), [
                'field' => $fieldName,
                'path' => $path
            ]);
        }

        return [
            'path' => $path,
            'url' => null,
            'name' => basename($path),
            'exists' => false
        ];
    }

    /**
     * Format bytes to human readable format.
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Clean all files associated with a model when deleting.
     * Call this method in your controller's afterDestroy hook.
     *
     * @param int|string $modelId Model ID
     * @return array Results of cleanup operations
     */
    protected function cleanupModelFiles(int|string $modelId): array
    {
        $results = [];
        $uploadConfig = $this->getNormalizedUploadConfig();

        foreach ($uploadConfig as $fieldName => $config) {
            if ($config['use_model_id'] ?? false) {
                if ($config['multiple'] ?? false) {
                    // Delete entire folder for multiple files
                    $results[$fieldName] = $this->deleteModelFolder($fieldName, $modelId);
                } else {
                    // Delete single file
                    $basePath = rtrim($config['path'] ?? 'uploads', '/');
                    $disk = $config['disk'] ?? 'public';
                    $results[$fieldName] = $this->deleteOldModelFile($fieldName, $basePath, $modelId, $disk);
                }
            }
        }

        if (array_filter($results)) {
            $this->logUploadSuccess("Model files cleanup completed", [
                'model_id' => $modelId,
                'results' => $results
            ]);
        }

        return $results;
    }

    /**
     * Filter AJAX upload fields and add necessary data attributes.
     * In create mode: removes AJAX upload fields completely.
     * In edit mode: adds data attributes for AJAX functionality.
     * Call this method in your beforeSendToTemplate hook.
     *
     * @param array &$templateData Template data reference
     * @param bool $isCreateMode Whether we're in create mode
     * @param int|string|null $modelId Model ID (required for edit mode)
     * @return array Filtered field keys that were removed (create mode only)
     */
    protected function filterAjaxUploadFields(array &$templateData, bool $isCreateMode, int|string|null $modelId = null): array
    {
        $removedFields = [];
        $uploadConfig = $this->getNormalizedUploadConfig();

        if (!isset($templateData['fields'])) {
            return $removedFields;
        }

        if ($isCreateMode) {
            // Create mode: Remove AJAX upload fields completely
            $templateData['fields'] = array_filter($templateData['fields'], function($field) use ($uploadConfig, &$removedFields) {
                $fieldName = $field->key ?? null;

                if ($fieldName && isset($uploadConfig[$fieldName])) {
                    $config = $uploadConfig[$fieldName];

                    if ($config['ajax_upload'] ?? false) {
                        $removedFields[] = $fieldName;

                        $this->logUploadSuccess("AJAX upload field filtered from create mode", [
                            'field' => $fieldName,
                            'mode' => 'create'
                        ]);

                        return false; // Remove this field
                    }
                }

                return true; // Keep this field
            });

            // Re-index array to maintain proper structure
            $templateData['fields'] = array_values($templateData['fields']);
        } else {
            // Edit mode: Add AJAX upload data attributes to relevant fields
            foreach ($templateData['fields'] as $field) {
                $fieldName = $field->key ?? null;

                if ($fieldName && isset($uploadConfig[$fieldName])) {
                    $config = $uploadConfig[$fieldName];

                    if ($config['ajax_upload'] ?? false) {
                        // Get current attributes
                        $existingAttributes = $field->attributes ?? [];

                        // Add AJAX upload data attributes
                        $existingAttributes['data-ajax-upload'] = 'true';
                        $existingAttributes['data-model-id'] = (string)$modelId;
                        $existingAttributes['data-field-name'] = $fieldName;

                        // Add upload configuration data
                        if (isset($config['multiple']) && $config['multiple']) {
                            $existingAttributes['data-multiple'] = 'true';
                        }

                        if (isset($config['max_size'])) {
                            $existingAttributes['data-max-size'] = $config['max_size'];
                        }

                        if (isset($config['types']) && !empty($config['types'])) {
                            $existingAttributes['accept'] = '.' . implode(',.', $config['types']);
                        }

                        // Update field attributes
                        $field->withAttributes($existingAttributes);

                        $this->logUploadSuccess("AJAX upload data attributes added to field", [
                            'field' => $fieldName,
                            'model_id' => $modelId,
                            'mode' => 'edit',
                            'attributes_added' => ['data-ajax-upload', 'data-model-id', 'data-field-name']
                        ]);
                    }
                }
            }
        }

        return $removedFields;
    }

    /**
     * Check if field should use AJAX upload.
     *
     * @param string $fieldName
     * @return bool
     */
    protected function shouldUseAjaxUpload(string $fieldName): bool
    {
        $config = $this->getFieldUploadConfig($fieldName);
        return $config['ajax_upload'] ?? false;
    }

    /**
     * Get AJAX upload fields that should be shown in edit mode.
     *
     * @return array Array of field names that use AJAX upload
     */
    protected function getAjaxUploadFields(): array
    {
        $ajaxFields = [];
        $uploadConfig = $this->getNormalizedUploadConfig();

        foreach ($uploadConfig as $fieldName => $config) {
            if ($config['ajax_upload'] ?? false) {
                $ajaxFields[] = $fieldName;
            }
        }

        return $ajaxFields;
    }

    /**
     * Handle AJAX file upload endpoint.
     * This method should be called from your controller's AJAX upload route.
     *
     * @param Request $request
     * @param int|string $id Model ID
     * @param string $fieldName Field name
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleAjaxUpload(Request $request, int|string $id, string $fieldName)
    {
        try {
            // Validate field configuration
            $config = $this->getFieldUploadConfig($fieldName);
            if (empty($config) || !($config['ajax_upload'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'فیلد مورد نظر برای آپلود AJAX پشتیبانی نمی‌شود.'
                ], 400);
            }

            // Validate file exists in request
            if (!$request->hasFile($fieldName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'فایلی برای آپلود انتخاب نشده است.'
                ], 400);
            }

            // Process upload
            $uploadedFiles = $this->processAjaxFileUploads($request, $id);

            if (empty($uploadedFiles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطا در آپلود فایل. لطفاً دوباره تلاش کنید.'
                ], 500);
            }

            // Update model with new file path(s)
            if (method_exists($this, 'model')) {
                $model = $this->model()::find($id);
                if ($model) {
                    $filePath = $uploadedFiles[$fieldName] ?? null;
                    if ($filePath) {
                        if ($config['multiple'] ?? false) {
                            // برای فایل‌های multiple، فایل‌های جدید را به فایل‌های موجود merge کن
                            $existingFiles = $model->{$fieldName} ?? [];
                            
                            // اطمینان از اینکه آرایه است
                            if (!is_array($existingFiles)) {
                                $existingFiles = [];
                            }
                            
                            // فایل‌های جدید را به آرایه موجود اضافه کن
                            $newFiles = is_array($filePath) ? $filePath : [$filePath];
                            $mergedFiles = array_merge($existingFiles, $newFiles);
                            
                            // حذف فایل‌های تکراری (اختیاری)
                            $mergedFiles = array_unique($mergedFiles);
                            
                            // ذخیره فایل‌های merged
                            $model->{$fieldName} = array_values($mergedFiles); // array_values برای reindex کردن keys
                        } else {
                            // برای single file، روش قدیمی که override می‌کند
                            $model->{$fieldName} = is_array($filePath) ? $filePath[0] : $filePath;
                        }
                        $model->save();
                    }
                }
            }

            // Get updated model data for response (after merge)
            $updatedModel = null;
            $allFiles = $uploadedFiles[$fieldName] ?? null;
            if (method_exists($this, 'model')) {
                $updatedModel = $this->model()::find($id);
                if ($updatedModel && ($config['multiple'] ?? false)) {
                    // برای multiple، فایل‌های کامل merged را برگردان
                    $allFiles = $updatedModel->{$fieldName} ?? [];
                }
            }
            
            // Prepare response data
            $isMultiple = $config['multiple'] ?? false;
            $newFilesCount = is_array($uploadedFiles[$fieldName] ?? null) ? count($uploadedFiles[$fieldName]) : 1;
            $totalFilesCount = is_array($allFiles) ? count($allFiles) : 1;
            
            $message = $isMultiple ? 
                "${newFilesCount} فایل جدید آپلود شد. مجموع: ${totalFilesCount} فایل" : 
                'فایل با موفقیت آپلود شد.';
            
            $responseData = [
                'success' => true,
                'message' => $message,
                'field' => $fieldName,
                'model_id' => $id,
                'uploaded_files' => $uploadedFiles[$fieldName] ?? null, // فایل‌های جدید
                'all_files' => $allFiles, // فایل‌های کامل (existing + new)
                'multiple' => $isMultiple,
                'new_files_count' => $newFilesCount,
                'total_files_count' => $totalFilesCount
            ];

            // Add file info for template display
            if (isset($uploadedFiles[$fieldName])) {
                if ($isMultiple && is_array($uploadedFiles[$fieldName])) {
                    // Multiple files - create array of file info
                    $fileInfos = [];
                    foreach ($uploadedFiles[$fieldName] as $filePath) {
                        $fileInfo = $this->getFileInfoForTemplate($fieldName, $filePath);
                        if ($fileInfo) {
                            $fileInfos[] = $fileInfo;
                        }
                    }
                    $responseData['file_info'] = $fileInfos;
                } else {
                    // Single file
                    $filePath = is_array($uploadedFiles[$fieldName]) ? $uploadedFiles[$fieldName][0] : $uploadedFiles[$fieldName];
                    $fileInfo = $this->getFileInfoForTemplate($fieldName, $filePath);
                    if ($fileInfo) {
                        $responseData['file_info'] = $fileInfo;
                    }
                }
            }

            $this->logUploadSuccess("AJAX upload completed successfully", [
                'field' => $fieldName,
                'model_id' => $id,
                'files_count' => is_array($uploadedFiles[$fieldName] ?? null) ? count($uploadedFiles[$fieldName]) : 1
            ]);

            return response()->json($responseData);

        } catch (\Exception $e) {
            $this->logUploadError("AJAX upload failed: " . $e->getMessage(), [
                'field' => $fieldName,
                'model_id' => $id,
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطای سیستم در آپلود فایل.'
            ], 500);
        }
    }

    /**
     * Handle AJAX file deletion.
     *
     * @param Request $request
     * @param int|string $id Model ID
     * @param string $fieldName Field name
     * @param string $filePath File path to delete
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleAjaxDelete(Request $request, int|string $id, string $fieldName, string $filePath)
    {
        try {
            // Validate field configuration exists
            $config = $this->getFieldUploadConfig($fieldName);
            if (empty($config)) {
                return response()->json([
                    'success' => false,
                    'message' => 'پیکربندی آپلود برای این فیلد یافت نشد.'
                ], 400);
            }
            
            // حذف AJAX برای هر فیلد آپلودی مجاز است، صرف‌نظر از ajax_upload

            // Delete file
            $deleted = $this->deleteOldFile($fieldName, $filePath);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطا در حذف فایل.'
                ], 500);
            }

            // Update model (remove file path)
            if (method_exists($this, 'model')) {
                $model = $this->model()::find($id);
                if ($model) {
                    if ($config['multiple'] ?? false) {
                        // Handle multiple files - get as array (accessor handles JSON decode)
                        $currentPaths = $model->{$fieldName} ?? [];
                        
                        // Ensure it's an array
                        if (!is_array($currentPaths)) {
                            $currentPaths = [];
                        }
                        
                        // Remove the deleted file from array
                        $currentPaths = array_filter($currentPaths, fn($path) => $path !== $filePath);
                        
                        // Set new value (mutator handles JSON encode)
                        $model->{$fieldName} = empty($currentPaths) ? [] : array_values($currentPaths);
                    } else {
                        // Handle single file
                        if ($model->{$fieldName} === $filePath) {
                            $model->{$fieldName} = null;
                        }
                    }
                    $model->save();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'فایل با موفقیت حذف شد.',
                'field' => $fieldName,
                'model_id' => $id,
                'deleted_file' => $filePath
            ]);

        } catch (\Exception $e) {
            $this->logUploadError("AJAX delete failed: " . $e->getMessage(), [
                'field' => $fieldName,
                'model_id' => $id,
                'file_path' => $filePath
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطای سیستم در حذف فایل.'
            ], 500);
        }
    }

    /**
     * Public method for AJAX file upload endpoint.
     * This method is called by routes defined via RouteHelper.
     *
     * @param Request $request
     * @param int|string $id Model ID
     * @param string $fieldName Field name
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxUpload(Request $request, $id, string $fieldName)
    {
        return $this->handleAjaxUpload($request, $id, $fieldName);
    }

    /**
     * Public method for AJAX file delete endpoint.
     * This method is called by routes defined via RouteHelper.
     * Route pattern: /admin/{resource}/{id}/ajax-delete/{fieldName}?file_path=...
     * URL Example: /admin/users/304/ajax-delete/avatar?file_path=uploads%2Favatars%2F304.JPG
     *
     * @param Request $request HTTP request with file_path in query string
     * @param int|string $id Model ID (from route parameter)
     * @param string $fieldName Field name (from route parameter)
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxDeleteFile(Request $request, $id, $fieldName)
    {
        $filePath = $request->query('file_path');
        
        if (empty($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'مسیر فایل برای حذف مشخص نشده است.'
            ], 400);
        }
        
        return $this->handleAjaxDelete($request, $id, $fieldName, $filePath);
    }
/*
     * Set existing file data for image fields in edit mode.
     * This enables preview of uploaded files in the edit form.
     * Call this method from your beforeSendToTemplate hook for IMAGE fields.
     *
     * @param \RMS\Core\Data\Field $field The image field
     * @param mixed $modelId The model ID
     * @param mixed $model The model instance (optional for optimization)
     * @return void
     */
    protected function setExistingFileData(\RMS\Core\Data\Field $field, $modelId, $model = null): void
    {
        // Get upload config for this field
        $uploadConfig = $this->getNormalizedUploadConfig();
        $fieldName = $field->key;

        if (!isset($uploadConfig[$fieldName])) {
            $this->logUploadError("No upload configuration found for field: {$fieldName}", [
                'model_id' => $modelId,
                'available_configs' => array_keys($uploadConfig)
            ]);
            return; // No upload config for this field
        }

        $config = $uploadConfig[$fieldName];

        // Get the file path from model if available
        $filePath = null;
        if ($model && isset($model->{$fieldName})) {
            $filePath = $model->{$fieldName};
        } else {
            // Fallback: query using Model (not DB::table) to get proper casting
            try {
                if (method_exists($this, 'model')) {
                    $modelClass = $this->model();
                } else {
                    // Fallback to User model if no model method
                    $modelClass = '\App\Models\User';
                }
                
                $modelInstance = $modelClass::find($modelId);
                if ($modelInstance && isset($modelInstance->{$fieldName})) {
                    $filePath = $modelInstance->{$fieldName};
                }
            } catch (\Exception $e) {
                $this->logUploadError("Failed to query model data for existing file: " . $e->getMessage(), [
                    'field' => $fieldName,
                    'model_id' => $modelId
                ]);
                return;
            }
        }

        // If we have file data, set it up for preview
        if ($filePath && !empty($filePath) && (!is_string($filePath) || !empty(trim($filePath)))) {
            // Get the disk configuration
            $disk = $config['disk'] ?? 'public';
            $isMultiple = $config['multiple'] ?? false;

            try {
                if ($isMultiple) {
                    // Handle multiple files (JSON array)
                    $filePaths = is_string($filePath) ? json_decode($filePath, true) : $filePath;
                    
                    if (is_array($filePaths) && !empty($filePaths)) {
                        $existingFiles = [];
                        
                        foreach ($filePaths as $singlePath) {
                            if (Storage::disk($disk)->exists($singlePath)) {
                                $fileUrl = $this->getFileUrl($singlePath, $disk);
                                if ($fileUrl) {
                                    $fileSize = null;
                                    try {
                                        $fileSize = $this->formatBytes(Storage::disk($disk)->size($singlePath));
                                    } catch (\Exception $e) {
                                        // Size not critical
                                    }
                                    
                                    $existingFiles[] = [
                                        'url' => $fileUrl,
                                        'filename' => basename($singlePath),
                                        'path' => $singlePath,
                                        'size' => $fileSize
                                    ];
                                }
                            }
                        }
                        
                        if (!empty($existingFiles)) {
                            // Set the current value for the field
                            $field->withDefaultValue(json_encode($filePaths));
                            
                            // Add multiple files data as attributes with proper escaping
                            $existingAttributes = $field->attributes ?? [];
                            $existingAttributes['data-existing-files'] = htmlspecialchars(json_encode($existingFiles), ENT_QUOTES, 'UTF-8');
                            $field->withAttributes($existingAttributes);
                            
                            $this->logUploadSuccess("Multiple existing files data set for preview", [
                                'field' => $fieldName,
                                'model_id' => $modelId,
                                'files_count' => count($existingFiles),
                                'disk' => $disk
                            ]);
                        }
                    }
                } else {
                    // Handle single file
                    if (Storage::disk($disk)->exists($filePath)) {
                        // Get the full URL for preview
                        $fileUrl = $this->getFileUrl($filePath, $disk);

                        if ($fileUrl) {
                            // Set the current value for the field
                            $field->withDefaultValue($filePath);

                            // Add preview data as attributes for the image uploader JS
                            $existingAttributes = $field->attributes ?? [];
                            $existingAttributes['data-existing-file'] = $fileUrl;
                            $existingAttributes['data-existing-filename'] = basename($filePath);
                            $existingAttributes['data-existing-path'] = $filePath;

                            // Add file size if available
                            try {
                                $fileSize = Storage::disk($disk)->size($filePath);
                                $existingAttributes['data-existing-size'] = $this->formatBytes($fileSize);
                            } catch (\Exception $e) {
                                // Size not critical, continue without it
                            }

                            $field->withAttributes($existingAttributes);

                            $this->logUploadSuccess("Existing file data set for preview", [
                                'field' => $fieldName,
                                'model_id' => $modelId,
                                'file_path' => $filePath,
                                'file_url' => $fileUrl,
                                'disk' => $disk
                            ]);
                        }
                    } else {
                        // File doesn't exist, log warning but don't fail
                        $this->logUploadError("File referenced in database doesn't exist on disk", [
                            'field' => $fieldName,
                            'model_id' => $modelId,
                            'file_path' => $filePath,
                            'disk' => $disk
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $this->logUploadError("Failed to setup existing file preview: " . $e->getMessage(), [
                    'field' => $fieldName,
                    'model_id' => $modelId,
                    'file_path' => $filePath
                ]);
            }
        }
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
