<?php

declare(strict_types=1);

namespace RMS\Core\Traits\List;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
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
     * @return Response|string
     */
    public function generateList(): Response|string
    {
        $list = new Generator($this);
        $this->appendActions($list);
        
        // Call rendering pipeline
        $this->beforeRenderView();
        $this->beforeGenerateList($list);

        $this->view->withVariables(['list' => $list->generate()]);
        $this->setTplList();

        return $this->view();
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
     * Generate URL for boolean field toggling.
     *
     * @param int|string $id
     * @param string $key
     * @return string|null
     */
    public function boolFieldUrl(int|string $id, string $key): ?string
    {
        if ($this instanceof HasList) {
            return route(
                $this->prefix_route . $this->baseRoute() . '.' . $key,
                [$this->routeParameter() => $id, 'field' => $key]
            );
        }
        
        return null;
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
    public function index(Request $request): Response|string
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
        $generator->addAction(new Action(
            trans('admin.show'),
            $this->baseRoute() . '.show',
            config($this->theme . '.actions.show'),
            'show btn-outline-info'
        ));
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

        $action = (new Action(
            trans('admin.delete'),
            $this->baseRoute() . '.destroy',
            config($this->theme . '.actions.destroy'),
            'delete btn-outline-danger'
        ))->withConfirm($confirm);

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
        $generator->addAction(new Action(
            trans('admin.edit'),
            $this->baseRoute() . '.edit',
            config($this->theme . '.actions.edit'),
            'edit btn-outline-success'
        ));
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
}
