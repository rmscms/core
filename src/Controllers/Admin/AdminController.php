<?php

namespace RMS\Core\Controllers\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use RMS\Core\View\View;
use Illuminate\Filesystem\Filesystem;

abstract class AdminController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * The admin theme name.
     *
     * @var string
     */
    public $theme;

    /**
     * The view instance for rendering views.
     *
     * @var View
     */
    public $view;

    public function __construct(Filesystem $filesystem)
    {
        $this->middleware('auth:admin');
        $this->theme = config('cms.admin_theme', 'admin');
        $this->view = new View($filesystem, $this->theme);
    }

    /**
     * Get the currently authenticated admin user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    protected function getAdmin()
    {
        return Auth::guard('admin')->user();
    }

    /**
     * Set the view title.
     *
     * @param string $title
     * @return $this
     */
    protected function title($title)
    {
        $this->view->withVariables(['title' => $title]);
        return $this;
    }

    /**
     * Hook to run before rendering the view.
     *
     * @return void
     */
    protected function beforeRenderView()
    {
        $this->view->withVariables([
            'user' => $this->getAdmin(),
            'controller' => $this,
            'theme' => $this->theme,
        ]);

        $this->view->withJsVariables([
            'base_url' => url('/'),
        ]);
    }

    /**
     * Render view.
     *
     * @param bool $usePackageNamespace
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * @throws \RuntimeException
     */
    protected function view($usePackageNamespace = true)
    {
        $this->beforeRenderView();
        if (!$this->view->hasTpl()) {
            throw new \RuntimeException('View template is not set.');
        }
        return $this->view->render($usePackageNamespace);
    }
}
