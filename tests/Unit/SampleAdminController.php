<?php

namespace RMS\Core\Tests\Unit;

use RMS\Core\Controllers\Admin\AdminController;
use RMS\Core\Data\Field;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Sample admin controller for testing AdminController functionality.
 */
class SampleAdminController extends AdminController
{
    /**
     * Get form fields for this controller.
     *
     * @return array
     */
    public function getFieldsForm(): array
    {
        return [
            Field::make('id')->setDatabaseKey('id'),
            Field::make('name')->setDatabaseKey('name'),
            Field::make('email')->setDatabaseKey('email')
        ];
    }

    /**
     * Get list fields for this controller.
     *
     * @return array
     */
    public function getListFields(): array
    {
        return [
            Field::make('id')->setDatabaseKey('id'),
            Field::make('name')->setDatabaseKey('name'),
            Field::make('email')->setDatabaseKey('email'),
            Field::make('created_at')->setDatabaseKey('created_at')
        ];
    }

    /**
     * Return the table name for data retrieval.
     *
     * @return string
     */
    public function table(): string
    {
        return 'users';
    }

    /**
     * Load a specific model instance by ID or return a new instance.
     *
     * @param mixed|null $id
     * @return Model
     */
    public function model($id = null): Model
    {
        // Create a mock Eloquent model for testing
        $model = new class extends Model {
            protected $table = 'users';
            protected $fillable = ['id', 'name', 'email'];
            public $timestamps = false;
            
            public function toArray(): array
            {
                return $this->attributes;
            }
        };
        
        if ($id) {
            $model->id = $id;
            $model->name = 'Test User';
            $model->email = 'test@example.com';
        }
        
        return $model;
    }

    /**
     * Return the model class name.
     *
     * @return string
     */
    public function modelName(): string
    {
        return 'App\\Models\\User';
    }

    /**
     * Get the base route for this controller.
     *
     * @return string
     */
    public function baseRoute(): string
    {
        return 'admin.users';
    }

    /**
     * Get the route parameter name.
     *
     * @return string
     */
    public function routeParameter(): string
    {
        return 'user';
    }

    /**
     * Get form URL for submissions.
     *
     * @return string
     */
    public function formUrl(): string
    {
        return route('admin.users.store');
    }

    /**
     * Set form template.
     *
     * @return void
     */
    public function setTplForm(): void
    {
        // Mock implementation
    }

    /**
     * Get validation rules for the form.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email'
        ];
    }

    /**
     * Get form configuration.
     *
     * @return array
     */
    public function getFormConfig(): array
    {
        return [
            'method' => 'POST',
            'enctype' => 'multipart/form-data'
        ];
    }

    /**
     * Set list template.
     *
     * @return void
     */
    public function setTplList(): void
    {
        // Mock implementation
    }

    /**
     * Get list configuration.
     *
     * @return array
     */
    public function getListConfig(): array
    {
        return [
            'pagination' => true,
            'per_page' => 15,
            'sortable' => true
        ];
    }

    /**
     * Get per page value.
     *
     * @return int
     */
    public function getPerPage(): int
    {
        return 15;
    }
}
