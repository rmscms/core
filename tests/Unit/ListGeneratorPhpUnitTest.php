<?php

namespace RMS\Core\Tests\Unit;

use Tests\TestCase;
use RMS\Core\Data\ListGenerator;
use RMS\Core\Data\Field;
use RMS\Core\Data\Link;
use RMS\Core\Data\ListResponse;
use RMS\Core\Data\Database;
use RMS\Core\Contracts\List\HasList;
use RMS\Core\Contracts\Data\UseDatabase;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class ListGeneratorPhpUnitTest extends TestCase
{
    private $mockList;
    private $mockDatabaseList;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock list that implements HasList
        $this->mockList = new class implements HasList {
            public function getListFields(): array
            {
                return [
                    Field::make('id')->setDatabaseKey('id'),
                    Field::make('name')->setDatabaseKey('name'),
                    Field::make('email')->setDatabaseKey('email')
                ];
            }
            
            public function baseRoute(): string
            {
                return 'test.list';
            }
            
            public function routeParameter(): string
            {
                return 'id';
            }
            
            public function setTplList(): void
            {
                // Mock implementation
            }
            
            public function getListConfig(): array
            {
                return [
                    'pagination' => true,
                    'per_page' => 15,
                    'sortable' => true
                ];
            }
            
            public function getPerPage(): int
            {
                return 15;
            }
        };

        // Mock list with database support
        $this->mockDatabaseList = new class implements HasList, UseDatabase {
            public function getListFields(): array
            {
                return [
                    Field::make('id')->setDatabaseKey('id'),
                    Field::make('name')->setDatabaseKey('name'),
                    Field::make('email')->setDatabaseKey('email')
                ];
            }
            
            public function baseRoute(): string
            {
                return 'test.database-list';
            }
            
            public function routeParameter(): string
            {
                return 'id';
            }
            
            public function setTplList(): void
            {
                // Mock implementation
            }
            
            public function getListConfig(): array
            {
                return [
                    'pagination' => true,
                    'per_page' => 20,
                    'sortable' => true
                ];
            }
            
            public function getPerPage(): int
            {
                return 20;
            }

            public function table(): string
            {
                return 'users';
            }
            
            public function query(Builder $query): void
            {
                // Mock query modifications
                $query->where('active', 1);
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

    public function test_can_be_instantiated_with_valid_list()
    {
        $generator = new ListGenerator($this->mockList);
        
        $this->assertInstanceOf(ListGenerator::class, $generator);
        $this->assertSame($this->mockList, $generator->getList());
        $this->assertCount(3, $generator->getFields());
    }

    public function test_can_be_created_with_static_make_method()
    {
        $generator = ListGenerator::make($this->mockList);
        
        $this->assertInstanceOf(ListGenerator::class, $generator);
    }

    public function test_generate_throws_exception_for_non_database_lists()
    {
        $generator = new ListGenerator($this->mockList);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This class should implement: RMS\Core\Contracts\Data\UseDatabase');
        
        $generator->generate();
    }

    public function test_initializes_database_for_use_database_lists()
    {
        $generator = new ListGenerator($this->mockDatabaseList);
        
        $this->assertInstanceOf(Database::class, $generator->getDatabase());
    }

    public function test_does_not_initialize_database_for_regular_lists()
    {
        $generator = new ListGenerator($this->mockList);
        
        $this->assertNull($generator->getDatabase());
    }

    public function test_can_add_links_with_fluent_interface()
    {
        $generator = new ListGenerator($this->mockList);
        $link = new Link('Add New', '/add', 'success');
        $result = $generator->link($link);
        
        $this->assertSame($generator, $result);
        $this->assertCount(1, $generator->getLinks());
    }

    public function test_can_set_per_page()
    {
        // Create a mock that returns 0 from getPerPage() to test setPerPage functionality
        $mockListWithZeroPerPage = new class implements HasList {
            public function getListFields(): array
            {
                return [
                    Field::make('id')->setDatabaseKey('id'),
                    Field::make('name')->setDatabaseKey('name')
                ];
            }
            
            public function baseRoute(): string
            {
                return 'test.list';
            }
            
            public function routeParameter(): string
            {
                return 'id';
            }
            
            public function setTplList(): void
            {
                // Mock implementation
            }
            
            public function getListConfig(): array
            {
                return ['pagination' => true, 'sortable' => true];
            }
            
            public function getPerPage(): int
            {
                return 0; // Returns 0 so setPerPage value will be used
            }
        };
        
        $generator = new ListGenerator($mockListWithZeroPerPage);
        $result = $generator->setPerPage(25);
        
        $this->assertSame($generator, $result);
        $this->assertEquals(25, $generator->perPage());
    }

    public function test_can_use_simple_pagination()
    {
        $generator = new ListGenerator($this->mockList);
        
        // Test enabling simple pagination
        $result = $generator->useSimplePagination(true);
        $this->assertSame($generator, $result);
        
        // Test disabling simple pagination
        $generator->useSimplePagination(false);
        $this->assertSame($generator, $generator);
    }

    public function test_get_per_page_from_list_config()
    {
        $generator = new ListGenerator($this->mockDatabaseList);
        
        // Should get per_page from the list's getPerPage method
        $this->assertEquals(20, $generator->perPage());
    }

    public function test_can_enable_disable_create_button()
    {
        $generator = new ListGenerator($this->mockList);
        
        // Test disabling create button
        $result = $generator->showCreateButton(false);
        $this->assertSame($generator, $result);
        $this->assertFalse($generator->hasCreateButton());
        
        // Test enabling create button
        $generator->showCreateButton(true);
        $this->assertTrue($generator->hasCreateButton());
    }
}
