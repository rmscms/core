<?php

declare(strict_types=1);

namespace RMS\Core\Traits\List;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RMS\Core\Contracts\List\HasList;
use RMS\Core\Data\Action;
use RMS\Core\Data\BatchAction;
use RMS\Core\Data\Confirm;
use RMS\Core\View\HelperList\Generator;

/**
 * Trait for generating and managing lists with actions.
 *
 * @package RMS\Core\Traits\List
 */
trait GenerateList
{
    /**
     * Generate the list view with all configured features.
     *
     * @return View
     */
    public function generateList(): View
    {
        $list = new Generator($this);
        $this->appendActions($list);

        // Call rendering pipeline
        $this->beforeRenderView();
        $this->beforeGenerateList($list);

        $listResponse = $list->generate();
        // change data before send to template
        $this->beforeSendToListTemplate($listResponse);
        $this->view->withVariables([
            'list' => $listResponse,
            'generator' => $list,
            'listData' => $listResponse->toBladeData(), // Blade-optimized data structure
        ]);

        $this->setTplList();

        return $this->view();
    }
    protected function beforeSendToListTemplate(&$listResponse): void
    {

    }
    /**
     * Hook method called before list generation.
     * Override this method to customize list behavior.
     *
     * @param Generator $generator
     * @return void
     */
    protected function beforeGenerateList(Generator &$generator): void
    {
        // Override in child classes
    }

    /**
     * Get the route parameter name for this controller.
     *
     * @return string
     */
    public function routeParameter(): string
    {
        return Str::singular($this->baseRoute());
    }

    /**
     * Set the default template for list view.
     *
     * @return void
     */
    public function setTplList(): void
    {
        if (!$this->view->hasTpl()) {
            $this->view->setTpl('list.index');
        }
    }

    /**
     * Default index method for listing resources.
     *
     * @param Request $request
     * @return Response|string
     */
    public function index(Request $request)
    {
        return $this->generateList();
    }

    /**
     * Append default actions to the list generator.
     *
     * @param Generator $generator
     * @return void
     */
    protected function appendActions(Generator &$generator): void
    {
        $this->editAction($generator);
        $this->showAction($generator);
        $this->deleteAction($generator);
        $this->batchActiveAction($generator);
    }

    /**
     * Add show action to the generator.
     *
     * @param Generator $generator
     * @return void
     */
    protected function showAction(Generator &$generator): void
    {
        // Only add show action if controller has show method
        if (method_exists($this, 'show')) {
            $routeName = $this->prefix_route . $this->baseRoute() . '.show';
            $generator->addAction((new Action(
                trans('admin.show'),
                $routeName,
                config($this->theme . '.actions.show'),
                'show btn-outline-info'
            ))->withMethod('GET'));
        }
    }

    /**
     * Add delete action to the generator.
     *
     * @param Generator $generator
     * @return void
     */
    protected function deleteAction(Generator &$generator): void
    {
        $confirm = (new Confirm(
            trans('admin.are_u_sure'),
            trans('admin.action_can_not_undone'),
            'warning',
            'delete'
        ))
        ->confirmButton('مطمئن هستم')
        ->cancelButton('خیر');

        $routeName = $this->prefix_route . $this->baseRoute() . '.destroy';
        $action = (new Action(
            trans('admin.delete'),
            $routeName,
            config($this->theme . '.actions.destroy'),
            'delete btn-outline-danger'
        ))->withConfirm($confirm)->withMethod('DELETE');

        $generator->addAction($action);

        // Add batch delete action if enabled
        if ($generator->batch_destroy) {
            $batchAction = (new BatchAction('حذف دسته‌جمعی', 'destroy', 'btn-danger'))
                ->confirm($confirm);
            $generator->addBatchAction($batchAction);
        }
    }

    /**
     * Add edit action to the generator.
     *
     * @param Generator $generator
     * @return void
     */
    protected function editAction(Generator &$generator): void
    {
        $routeName = $this->prefix_route . $this->baseRoute() . '.edit';
        $generator->addAction((new Action(
            trans('admin.edit'),
            $routeName,
            config($this->theme . '.actions.edit'),
            'edit btn-outline-success'
        ))->withMethod('GET'));
    }

    /**
     * Add batch active action to the generator.
     *
     * @param Generator $generator
     * @return void
     */
    protected function batchActiveAction(Generator &$generator): void
    {
        if ($generator->batch_active) {
            $generator->addBatchAction(new BatchAction(
                trans('admin.batch_active'),
                'active',
                'btn-info'
            ));
        }
    }

    /**
     * Get the default list configuration.
     * Override this method in child classes to customize list behavior.
     *
     * @return array
     */
    public function getListConfig(): array
    {
        return [
            'per_page' => method_exists($this, 'getPerPage') ? $this->getPerPage() : 15,
            'sortable' => true,
            'searchable' => true,
            'filterable' => $this instanceof \RMS\Core\Contracts\Filter\ShouldFilter,
            'exportable' => $this instanceof \RMS\Core\Contracts\Export\ShouldExport,
            'batchable' => $this instanceof \RMS\Core\Contracts\Batch\HasBatch,
            'stats' => $this instanceof \RMS\Core\Contracts\Stats\HasStats
        ];
    }
}
