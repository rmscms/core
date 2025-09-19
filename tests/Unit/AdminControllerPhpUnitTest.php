<?php

namespace RMS\Core\Tests\Unit;

use Tests\TestCase;
use RMS\Core\Controllers\Admin\AdminController;
use RMS\Core\Contracts\Data\UseDatabase;
use RMS\Core\View\View;
use Illuminate\Filesystem\Filesystem;
use Mockery;

class AdminControllerPhpUnitTest extends TestCase
{
    private $controller;
    private $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the filesystem
        $this->filesystem = Mockery::mock(Filesystem::class);
        
        // Create instance of SampleAdminController for testing
        $this->controller = new class($this->filesystem) extends AdminController {
            /**
             * Get form fields for this controller.
             */
            public function getFieldsForm(): array
            {
                return [
                    \RMS\Core\Data\Field::make('id')->setDatabaseKey('id'),
                    \RMS\Core\Data\Field::make('name')->setDatabaseKey('name'),
                    \RMS\Core\Data\Field::make('email')->setDatabaseKey('email')
                ];
            }

            /**
             * Get list fields for this controller.
             */
            public function getListFields(): array
            {
                return [
                    \RMS\Core\Data\Field::make('id')->setDatabaseKey('id'),
                    \RMS\Core\Data\Field::make('name')->setDatabaseKey('name'),
                    \RMS\Core\Data\Field::make('email')->setDatabaseKey('email')
                ];
            }

            /**
             * Return the table name for data retrieval.
             */
            public function table(): string
            {
                return 'users';
            }

            /**
             * Load a specific model instance by ID or return a new instance.
             */
            public function model($id = null): \Illuminate\Database\Eloquent\Model
            {
                // Create a mock Eloquent model for testing
                $model = new class extends \Illuminate\Database\Eloquent\Model {
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
             */
            public function modelName(): string
            {
                return 'App\\Models\\User';
            }

            /**
             * Get the base route for this controller.
             */
            public function baseRoute(): string
            {
                return 'admin.users';
            }

            /**
             * Get the route parameter name.
             */
            public function routeParameter(): string
            {
                return 'user';
            }

            /**
             * Get form URL for submissions.
             */
            public function formUrl(): string
            {
                return '/admin/users';
            }

            /**
             * Set form template.
             */
            public function setTplForm(): string
            {
                return 'form';
            }

            /**
             * Get validation rules for the form.
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
             */
            public function setTplList(): void
            {
                // Mock implementation
            }

            /**
             * Get list configuration.
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
             */
            public function getPerPage(): int
            {
                return 15;
            }
        };
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_admin_controller_implements_use_database_interface()
    {
        $this->assertInstanceOf(UseDatabase::class, $this->controller);
    }

    public function test_admin_controller_has_form_and_list_trait()
    {
        // Check that controller has methods from FormAndList trait
        $this->assertTrue(method_exists($this->controller, 'setTitle'));
        $this->assertTrue(method_exists($this->controller, 'getTitle'));
        $this->assertTrue(method_exists($this->controller, 'setRoutePrefix'));
        $this->assertTrue(method_exists($this->controller, 'setTheme'));
        $this->assertTrue(method_exists($this->controller, 'getConfiguration'));
    }

    public function test_can_set_and_get_title()
    {
        $result = $this->controller->setTitle('Test Title');
        
        $this->assertSame($this->controller, $result);
        $this->assertEquals('Test Title', $this->controller->getTitle());
    }

    public function test_can_set_route_prefix()
    {
        $result = $this->controller->setRoutePrefix('custom.');
        
        $this->assertSame($this->controller, $result);
    }

    public function test_can_set_theme()
    {
        $result = $this->controller->setTheme('custom-theme');
        
        $this->assertSame($this->controller, $result);
    }

    public function test_implements_use_database_methods()
    {
        // Test table method
        $this->assertEquals('users', $this->controller->table());
        
        // Test modelName method
        $this->assertEquals('App\\Models\\User', $this->controller->modelName());
        
        // Test model method
        $model = $this->controller->model();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, $model);
        
        // Test model with ID
        $modelWithId = $this->controller->model(123);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, $modelWithId);
    }

    public function test_get_configuration_returns_array()
    {
        $this->controller->setTitle('Test Controller');
        $config = $this->controller->getConfiguration();
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('title', $config);
        $this->assertArrayHasKey('theme', $config);
        $this->assertArrayHasKey('prefix_route', $config);
        $this->assertArrayHasKey('base_route', $config);
        $this->assertArrayHasKey('model_name', $config);
        $this->assertArrayHasKey('features', $config);
        
        $this->assertEquals('Test Controller', $config['title']);
        $this->assertEquals('admin.users', $config['base_route']);
        $this->assertEquals('App\\Models\\User', $config['model_name']);
    }

    public function test_has_form_fields_method()
    {
        $fields = $this->controller->getFieldsForm();
        
        $this->assertIsArray($fields);
        $this->assertCount(3, $fields);
        
        foreach ($fields as $field) {
            $this->assertInstanceOf(\RMS\Core\Data\Field::class, $field);
        }
    }

    public function test_has_list_fields_method()
    {
        $fields = $this->controller->getListFields();
        
        $this->assertIsArray($fields);
        $this->assertCount(3, $fields);
        
        foreach ($fields as $field) {
            $this->assertInstanceOf(\RMS\Core\Data\Field::class, $field);
        }
    }

    public function test_query_method_can_be_called_without_error()
    {
        $builder = Mockery::mock(\Illuminate\Database\Query\Builder::class);
        
        // Should not throw any exception
        $this->controller->query($builder);
        
        $this->assertTrue(true); // Test passes if no exception thrown
    }
}
