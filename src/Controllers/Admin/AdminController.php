<?php

namespace RMS\Core\Controllers\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use RMS\Core\View\View;
use Illuminate\Filesystem\Filesystem;
use RMS\Core\Traits\FormAndList;
use RMS\Core\Contracts\Data\UseDatabase;

abstract class AdminController extends Controller implements UseDatabase
{
    use AuthorizesRequests, ValidatesRequests, FormAndList;

    public $admin;

    public function __construct(Filesystem $filesystem)
    {
        $this->middleware('auth:admin');
        $this->theme = config('cms.admin_theme', 'admin');
        $this->view = new View($filesystem, $this->theme);
        
        // Set default per-page to 20 for admin controllers
        if (method_exists($this, 'setDefaultPerPage')) {
            $this->setDefaultPerPage(20);
        }
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
        
        // Load core plugins
        $this->view->withPlugins(['sweetalert2', 'sidebar-mobile', 'mobile-footer-nav']);
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

    /**
     * Return the table name for data retrieval.
     * Child classes must implement this method.
     *
     * @return string
     */
    abstract public function table(): string;

    /**
     * Modify the query builder instance.
     * Child classes can override this method to add custom query logic.
     *
     * @param \Illuminate\Database\Query\Builder $sql
     * @return void
     */
    public function query(\Illuminate\Database\Query\Builder $sql): void
    {
        // Apply static filters if the controller defines any
        if (method_exists($this, 'getStaticFilters')) {
            $staticFilters = $this->getStaticFilters();
            if (!empty($staticFilters)) {
                foreach ($staticFilters as $filter) {
                    // Ensure it's a FilterDatabase object with applyToQuery method
                    if (is_object($filter) && method_exists($filter, 'applyToQuery')) {
                        $filter->applyToQuery($sql);
                    }
                }
            }
        }
        
        // Default implementation - child classes can override with parent::query($sql)
        // Common query modifications like soft deletes, active status, etc.
    }


    /**
     * Return the model class name.
     * Child classes must implement this method.
     *
     * @return string
     */
     abstract public function modelName(): string;

    /**
     * Set the form template.
     * Can be overridden by child classes for custom templates.
     *
     * @return void
     */
    public function setTplForm(): void
    {
        $this->view->setTpl('form.index');
    }

    /**
     * Set the list template.
     * Can be overridden by child classes for custom templates.
     *
     * @return void
     */
    public function setTplList(): void
    {
        $this->view->setTpl('list.index');
    }

    /**
     * Determine if the user is authorized to make form requests.
     * Override this method in child controllers for custom authorization.
     * This method satisfies RequestForm interface requirements.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function authorizeRequest(\Illuminate\Http\Request $request): bool
    {
        // Default: allow all authenticated admin users
        return auth('admin')->check();
    }
}
