<?php

namespace RMS\Core\Tests\Unit;

use Tests\TestCase;
use RMS\Core\Data\FormGenerator;
use RMS\Core\Data\Field;
use RMS\Core\Data\Link;
use RMS\Core\Data\FormResponse;
use RMS\Core\Data\Database;
use RMS\Core\Contracts\Form\HasForm;
use RMS\Core\Contracts\Data\UseDatabase;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class FormGeneratorPhpUnitTest extends TestCase
{
    private $mockForm;
    private $mockDatabaseForm;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock form that implements HasForm
        $this->mockForm = new class implements HasForm {
            public function getFieldsForm(): array
            {
                return [
                    Field::make('id')->setDatabaseKey('id'),
                    Field::make('name')->setDatabaseKey('name'),
                    Field::make('email')->setDatabaseKey('email')
                ];
            }
            
            public function formUrl(): string
            {
                return '/test/form';
            }
            
            public function setTplForm(): void
            {
                // Mock implementation
            }
            
            public function getValidationRules(): array
            {
                return ['name' => 'required', 'email' => 'email|required'];
            }
            
            public function getFormConfig(): array
            {
                return ['method' => 'POST', 'enctype' => 'multipart/form-data'];
            }
        };

        // Mock form with database support
        $this->mockDatabaseForm = new class implements HasForm, UseDatabase {
            public function getFieldsForm(): array
            {
                return [
                    Field::make('id')->setDatabaseKey('id'),
                    Field::make('name')->setDatabaseKey('name'),
                    Field::make('email')->setDatabaseKey('email')
                ];
            }
            
            public function formUrl(): string
            {
                return '/test/database-form';
            }
            
            public function setTplForm(): void
            {
                // Mock implementation
            }
            
            public function getValidationRules(): array
            {
                return ['name' => 'required', 'email' => 'email|required'];
            }
            
            public function getFormConfig(): array
            {
                return ['method' => 'POST', 'enctype' => 'application/x-www-form-urlencoded'];
            }

            public function table(): string
            {
                return 'users';
            }
            
            public function query(Builder $query): void
            {
                // Mock query modifications
            }

            public function model($id = null): Model
            {
                // Create a mock Eloquent model
                $model = new class extends Model {
                    protected $fillable = ['id', 'name', 'email'];
                    public $timestamps = false;
                    
                    public function toArray(): array
                    {
                        return $this->attributes;
                    }
                };
                
                // Set the attributes
                $model->id = $id ?? 1;
                $model->name = 'John Doe';
                $model->email = 'john@example.com';
                
                return $model;
            }
            
            public function modelName(): string
            {
                return 'App\\Models\\User';
            }

            public function getSecurityConstraints(): array
            {
                return [
                    ['column' => 'active', 'operator' => '=', 'value' => 1],
                    ['column' => 'deleted_at', 'operator' => 'IS NULL', 'value' => null]
                ];
            }
        };
    }

    public function test_can_be_instantiated_with_valid_form()
    {
        $generator = new FormGenerator($this->mockForm);
        
        $this->assertInstanceOf(FormGenerator::class, $generator);
        $this->assertSame($this->mockForm, $generator->getForm());
        $this->assertCount(3, $generator->getFields());
    }

    public function test_can_be_created_with_static_make_method()
    {
        $generator = FormGenerator::make($this->mockForm, 123);
        
        $this->assertInstanceOf(FormGenerator::class, $generator);
        $this->assertEquals(123, $generator->getId());
    }

    public function test_generates_form_response_correctly()
    {
        $generator = new FormGenerator($this->mockForm);
        $response = $generator->generate();
        
        $this->assertInstanceOf(FormResponse::class, $response);
    }

    public function test_initializes_database_for_use_database_forms()
    {
        $generator = new FormGenerator($this->mockDatabaseForm);
        
        $this->assertInstanceOf(Database::class, $generator->getDatabase());
    }

    public function test_does_not_initialize_database_for_regular_forms()
    {
        $generator = new FormGenerator($this->mockForm);
        
        $this->assertNull($generator->getDatabase());
    }

    public function test_can_add_links_with_fluent_interface()
    {
        $generator = new FormGenerator($this->mockForm);
        $link = new Link('Test Link', '/test', 'primary');
        $result = $generator->link($link);
        
        $this->assertSame($generator, $result);
        $this->assertCount(1, $generator->getLinks());
    }

    public function test_can_set_validation_rules()
    {
        $generator = new FormGenerator($this->mockForm);
        $rules = ['name' => 'required', 'email' => 'email|required'];
        $result = $generator->withValidation($rules);
        
        $this->assertSame($generator, $result);
        $this->assertEquals($rules, $generator->getValidationRules());
    }

    public function test_validates_field_types_in_constructor()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All fields must be instances of Field class');
        
        new FormGenerator($this->mockForm, null, ['invalid_field']);
    }
}
