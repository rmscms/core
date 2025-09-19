<?php

declare(strict_types=1);

namespace RMS\Core\Contracts\Upload;

/**
 * HasUploadConfig Interface
 * 
 * Contract for controllers that need file upload configuration.
 * Forces implementation of upload configuration method.
 * 
 * @package RMS\Core\Contracts\Upload
 * @version 1.0.0
 * @author RMS Core Team
 */
interface HasUploadConfig
{
    /**
     * Get upload configuration for each field.
     * This method must be implemented in your controller to configure file uploads.
     * 
     * @return array Configuration array for file fields
     * 
     * Example return:
     * [
     *     'avatar' => [
     *         'disk' => 'public',           // 'public' or 'local'
     *         'path' => 'uploads/avatars',  // relative path
     *         'types' => ['jpeg', 'jpg', 'png', 'gif', 'webp'],
     *         'max_size' => 2048,          // KB
     *         'multiple' => false,         // single file (uses model_id.ext)
     *         'use_model_id' => true,      // use model ID for filename
     *         'ajax_upload' => false,      // show field in create mode (normal upload)
     *         'dimensions' => ['width' => 800, 'height' => 600], // optional
     *     ],
     *     'gallery' => [
     *         'disk' => 'public',
     *         'path' => 'uploads/gallery',
     *         'types' => ['jpeg', 'jpg', 'png', 'gif', 'webp'],
     *         'max_size' => 5120,          // 5MB
     *         'multiple' => true,          // multiple files (creates folder per model)
     *         'use_model_id' => true,      // creates folder: uploads/gallery/123/
     *         'ajax_upload' => true,       // only show in edit mode (AJAX upload)
     *     ],
     *     'documents' => [
     *         'disk' => 'local',           // private storage
     *         'path' => 'documents/users',
     *         'types' => ['pdf', 'doc', 'docx'],
     *         'max_size' => 10240,         // 10MB
     *         'multiple' => false,
     *         'use_model_id' => false,     // use unique random names
     *         'ajax_upload' => false,      // normal upload with form
     *     ]
     * ];
     */
    public function getUploadConfig(): array;
}