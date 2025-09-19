<?php

declare(strict_types=1);

namespace RMS\Core\View;

use Illuminate\Filesystem\Filesystem;
use RMS\Core\Traits\View\ViewAssetManager;
use RMS\Core\Traits\View\ViewTemplateManager;
use RMS\Core\Traits\View\ViewUtilities;
use RMS\Core\Traits\View\ViewVariableManager;

/**
 * Enhanced View class for RMS Core with modern PHP features.
 * 
 * Uses trait-based architecture for better separation of concerns.
 * 
 * @package RMS\Core\View
 */
class View
{
    use ViewAssetManager;
    use ViewTemplateManager;
    use ViewVariableManager;
    use ViewUtilities;

    /**
     * Filesystem instance for file operations.
     */
    protected Filesystem $filesystem;

    /**
     * View constructor.
     *
     * @param Filesystem|null $file
     * @param string|null $theme
     */
    public function __construct(?Filesystem $file = null, ?string $theme = null)
    {
        $this->filesystem = $file ?? new Filesystem();
        $this->theme = $theme ?? config('cms.admin_theme', 'admin');
        $this->assetVersion = config('cms.asset_version', '1.0.0');
    }

    /**
     * Static factory method for creating View instances.
     *
     * @param string|null $theme
     * @return static
     */
    public static function make(?string $theme = null): static
    {
        return new static(null, $theme);
    }

    /**
     * Get filesystem instance.
     *
     * @return Filesystem
     */
    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * Set filesystem instance.
     *
     * @param Filesystem $filesystem
     * @return $this
     */
    public function setFilesystem(Filesystem $filesystem): self
    {
        $this->filesystem = $filesystem;
        return $this;
    }
}
