<?php

namespace RMS\Core\View;

use Illuminate\Filesystem\Filesystem;

class View
{
    /**
     * @var string|null
     */
    protected $theme;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $tpl;

    /**
     * @var array
     */
    protected $js = [];

    /**
     * @var array
     */
    protected $css = [];

    /**
     * @var array
     */
    protected $vars = [];

    /**
     * @var array
     */
    protected $js_vars = [];

    /**
     * View constructor.
     *
     * @param Filesystem $file
     * @param string|null $theme
     */
    public function __construct(Filesystem $file, $theme = null)
    {
        $this->filesystem = $file;
        $this->theme = $theme ?? config('cms.admin_theme', 'admin');
    }

    /**
     * Set theme folder.
     *
     * @param string $theme
     * @return $this
     */
    public function theme($theme)
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * Set view template.
     *
     * @param string $tpl
     * @return $this
     */
    public function setTpl($tpl)
    {
        $this->tpl = $tpl;
        return $this;
    }

    /**
     * Check if template is set.
     *
     * @return bool
     */
    public function hasTpl()
    {
        return (bool) $this->tpl;
    }

    /**
     * Add a JS file to view.
     *
     * @param string $js
     * @param bool $absolute
     * @return $this
     */
    public function withJs($js, $absolute = false)
    {
        return $this->withMultipleJs([
            ['path' => $js, 'absolute' => $absolute]
        ]);
    }

    /**
     * Add multiple JS files to view.
     *
     * @param array $js
     * @return $this
     */
    public function withMultipleJs(array $js)
    {
        foreach ($js as $file) {
            $file_path = (!isset($file['absolute']) || !$file['absolute'])
                ? $this->theme . '/js/' . $file['path']
                : $file['path'];
            if (in_array($file_path, $this->js)) {
                continue;
            }
            if ($this->filesystem->exists(public_path($file_path))) {
                $this->js[] = $file_path;
            }
        }
        return $this;
    }

    /**
     * Add a CSS file to view.
     *
     * @param string $css
     * @param bool $absolute
     * @return $this
     */
    public function withCss($css, $absolute = false)
    {
        return $this->withMultipleCss([
            ['path' => $css, 'absolute' => $absolute]
        ]);
    }

    /**
     * Add multiple CSS files to view.
     *
     * @param array $css
     * @return $this
     */
    public function withMultipleCss(array $css)
    {
        foreach ($css as $file) {
            $file_path = (!isset($file['absolute']) || !$file['absolute'])
                ? $this->theme . '/css/' . $file['path']
                : $file['path'];
            if (in_array($file_path, $this->css)) {
                continue;
            }
            if ($this->filesystem->exists(public_path($file_path))) {
                $this->css[] = $file_path;
            }
        }
        return $this;
    }

    /**
     * Add plugins to view.
     *
     * @param array $plugins
     * @return $this
     */
    public function withPlugins(array $plugins)
    {
        $css = [];
        $js = [];
        foreach ($plugins as $plugin) {
            $css[] = [
                'path' => $this->theme . '/plugins/' . $plugin . '/' . $plugin . '.css',
                'absolute' => true
            ];
            $js[] = [
                'path' => $this->theme . '/plugins/' . $plugin . '/' . $plugin . '.js',
                'absolute' => true
            ];
        }
        return $this->withMultipleJs($js)->withMultipleCss($css);
    }

    /**
     * Add variables to view.
     *
     * @param array $vars
     * @return $this
     */
    public function withVariables(array $vars)
    {
        $this->vars = array_merge($this->vars, $vars);
        return $this;
    }

    /**
     * Add JavaScript variables to view.
     *
     * @param array $vars
     * @return $this
     */
    public function withJsVariables(array $vars)
    {
        $this->js_vars = array_merge($vars, $this->js_vars);
        return $this;
    }

    /**
     * Render the view.
     *
     * @param bool $usePackageNamespace
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render($usePackageNamespace = true): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
    {
        $tpl = $usePackageNamespace ? 'cms::' . $this->theme . '.' . $this->tpl : $this->theme . '.' . $this->tpl;
        $parameters = array_merge([
            'css' => $this->css,
            'js' => $this->js,
            'theme' => $this->theme,
            'js_vars' => $this->js_vars
        ], $this->vars);
        return view($tpl, $parameters);
    }
}
