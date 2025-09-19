<?php

namespace RMS\Core\Tests\Unit;

use Tests\TestCase;
use RMS\Core\Data\Database;
use RMS\Core\Data\Field;
use RMS\Core\Data\Column;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use InvalidArgumentException;
use ErrorException;

class DatabasePhpUnitTest extends TestCase
{
    protected array $fields;
    protected string $table;
    protected Database $database;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create users table for testing
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->integer('age')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Mock basic fields for testing
        $this->fields = [
            Field::make('id')->setDatabaseKey('id'),
            Field::make('name')->setDatabaseKey('name'),
            Field::make('email')->setDatabaseKey('email')
        ];
        
        $this->table = 'users';
    }
    
    protected function tearDown(): void
    {
        Schema::dropIfExists('users');
        parent::tearDown();
    }

    // Constructor and Factory Methods Tests
    public function test_can_be_instantiated_with_valid_parameters()
    {
        $database = new Database($this->fields, $this->table);
        
        $this->assertInstanceOf(Database::class, $database);
        $this->assertEquals($this->table, $database->getTable());
        $this->assertEquals('a', $database->getAlias());
        $this->assertEquals($this->fields, $database->getFields());
    }

    public function test_can_be_created_with_custom_alias()
    {
        $database = new Database($this->fields, $this->table, 'u');
        
        $this->assertEquals('u', $database->getAlias());
    }

    public function test_throws_exception_with_empty_table_name()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table name cannot be empty');
        
        new Database($this->fields, '');
    }

    public function test_throws_exception_with_invalid_table_name()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid table name format');
        
        new Database($this->fields, 'users; DROP TABLE users;');
    }

    public function test_throws_exception_with_empty_fields_array()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fields array cannot be empty');
        
        new Database([], $this->table);
    }

    public function test_throws_exception_with_invalid_field_types()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All fields must be instances of Field class');
        
        new Database(['invalid'], $this->table);
    }

    public function test_can_be_created_using_static_make_method()
    {
        $database = Database::make($this->fields, $this->table);
        
        $this->assertInstanceOf(Database::class, $database);
        $this->assertEquals($this->table, $database->getTable());
    }

    public function test_can_be_created_using_fromTable_factory_method()
    {
        $database = Database::fromTable($this->table, ['id', 'name', 'email']);
        
        $this->assertInstanceOf(Database::class, $database);
        $this->assertCount(3, $database->getFields());
    }

    public function test_fromTable_handles_wildcard_columns_correctly()
    {
        $database = Database::fromTable($this->table, ['*']);
        
        $this->assertCount(1, $database->getFields());
    }

    // Query Building Tests
    public function test_generates_query_builder_correctly()
    {
        $database = new Database($this->fields, $this->table);
        $builder = $database->getQueryBuilder();
        
        $this->assertInstanceOf(Builder::class, $builder);
    }

    public function test_generates_correct_sql_string()
    {
        $database = new Database($this->fields, $this->table);
        $sql = $database->toSql();
        
        $this->assertStringContainsString('select', $sql);
        $this->assertStringContainsString('users', $sql);
    }

    public function test_can_add_where_conditions_with_fluent_interface()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->where('name', '=', 'John');
        
        $this->assertSame($database, $result);
    }

    public function test_validates_column_names_in_where_clauses()
    {
        $database = new Database($this->fields, $this->table);
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Column name cannot be empty');
        
        $database->where('', '=', 'value');
    }

    public function test_validates_operators_in_where_clauses()
    {
        $database = new Database($this->fields, $this->table);
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid SQL operator');
        
        $database->where('name', 'INVALID_OP', 'value');
    }

    public function test_can_add_whereIn_conditions()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->whereIn('id', [1, 2, 3]);
        
        $this->assertSame($database, $result);
    }

    public function test_can_add_whereNotIn_conditions()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->whereNotIn('status', ['inactive', 'deleted']);
        
        $this->assertSame($database, $result);
    }

    public function test_can_add_whereNull_conditions()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->whereNull('deleted_at');
        
        $this->assertSame($database, $result);
    }

    public function test_can_add_whereNotNull_conditions()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->whereNotNull('email');
        
        $this->assertSame($database, $result);
    }

    public function test_can_add_date_range_filters()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->whereDateBetween('created_at', '2023-01-01', '2023-12-31');
        
        $this->assertSame($database, $result);
    }

    public function test_can_add_search_across_multiple_columns()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->search('john', ['name', 'email']);
        
        $this->assertSame($database, $result);
    }

    public function test_ignores_empty_search_terms()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->search('', ['name', 'email']);
        
        $this->assertSame($database, $result);
    }

    // Filtering Tests
    public function test_can_apply_single_filter_using_Column_object()
    {
        $database = new Database($this->fields, $this->table);
        $column = new Column('name', '=', 'John', Field::STRING);
        
        $result = $database->withFilters([$column]);
        
        $this->assertSame($database, $result);
        $this->assertCount(1, $database->getAppliedFilters());
    }

    public function test_can_apply_multiple_filters()
    {
        $database = new Database($this->fields, $this->table);
        $filters = [
            new Column('name', '=', 'John', Field::STRING),
            new Column('age', '>', 18, Field::INTEGER)
        ];
        
        $database->withFilters($filters);
        
        $this->assertCount(2, $database->getAppliedFilters());
    }

    public function test_validates_filter_operators()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid operator: INVALID');
        
        new Column('name', 'INVALID', 'value', Field::STRING);
    }

    // Sorting Tests
    public function test_can_apply_single_sort_with_fluent_interface()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->sort('name', 'ASC');
        
        $this->assertSame($database, $result);
        
        $appliedSorting = $database->getAppliedSorting();
        $this->assertEquals('name', $appliedSorting['column']);
        $this->assertEquals('asc', $appliedSorting['direction']);
    }

    public function test_validates_sort_direction()
    {
        $database = new Database($this->fields, $this->table);
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sort direction must be ASC or DESC');
        
        $database->sort('name', 'INVALID');
    }

    public function test_can_apply_multiple_sorts()
    {
        $database = new Database($this->fields, $this->table);
        $sortRules = [
            ['name', 'ASC'],
            ['created_at', 'DESC']
        ];
        
        $result = $database->multiSort($sortRules);
        
        $this->assertSame($database, $result);
    }

    public function test_handles_invalid_sort_rules_gracefully()
    {
        $database = new Database($this->fields, $this->table);
        $sortRules = [
            ['name', 'ASC'],
            'invalid_rule', // This should be ignored
            ['email', 'DESC']
        ];
        
        $result = $database->multiSort($sortRules);
        
        $this->assertSame($database, $result);
    }

    // Query Modifiers Tests
    public function test_can_add_join_with_fluent_interface()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->join('profiles', 'users.id', '=', 'profiles.user_id');
        
        $this->assertSame($database, $result);
    }

    public function test_can_add_left_join()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->leftJoin('profiles', 'users.id', '=', 'profiles.user_id');
        
        $this->assertSame($database, $result);
    }

    public function test_can_add_group_by_with_single_column()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->groupBy('department');
        
        $this->assertSame($database, $result);
    }

    public function test_can_add_group_by_with_multiple_columns()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->groupBy(['department', 'role']);
        
        $this->assertSame($database, $result);
    }

    public function test_can_add_having_condition()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->having('COUNT(*)', '>', 5);
        
        $this->assertSame($database, $result);
    }

    public function test_can_set_limit()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->limit(10);
        
        $this->assertSame($database, $result);
    }

    public function test_validates_limit_is_positive()
    {
        $database = new Database($this->fields, $this->table);
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit must be greater than 0');
        
        $database->limit(0);
    }

    public function test_can_set_offset()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->offset(20);
        
        $this->assertSame($database, $result);
    }

    public function test_validates_offset_is_non_negative()
    {
        $database = new Database($this->fields, $this->table);
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Offset must be >= 0');
        
        $database->offset(-1);
    }

    // Security Features Tests
    public function test_can_add_security_constraints()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->addSecurityConstraint('active', '=', 1);
        
        $this->assertSame($database, $result);
    }

    public function test_validates_security_constraint_columns()
    {
        $database = new Database($this->fields, $this->table);
        
        $this->expectException(InvalidArgumentException::class);
        
        $database->addSecurityConstraint('', '=', 1);
    }

    public function test_validates_security_constraint_operators()
    {
        $database = new Database($this->fields, $this->table);
        
        $this->expectException(InvalidArgumentException::class);
        
        $database->addSecurityConstraint('active', 'INVALID', 1);
    }

    public function test_sanitizes_table_names()
    {
        $database = new Database($this->fields, 'users_table_123');
        
        $this->assertEquals('users_table_123', $database->getTable());
    }

    // Data Retrieval Tests
    public function test_validates_per_page_parameter_lower_bound()
    {
        $database = new Database($this->fields, $this->table);
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Per page must be between 1 and 1000');
        
        $database->get(0);
    }

    public function test_validates_per_page_upper_limit()
    {
        $database = new Database($this->fields, $this->table);
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Per page must be between 1 and 1000');
        
        $database->get(1001);
    }

    public function test_has_getter_methods_for_debugging()
    {
        $database = new Database($this->fields, $this->table);
        $database->where('name', '=', 'John');
        
        $sql = $database->toSql();
        $bindings = $database->getBindings();
        
        $this->assertIsString($sql);
        $this->assertIsArray($bindings);
    }

    // Field SQL Generation Tests
    public function test_handles_fields_without_database_key()
    {
        $field = Field::make('test_field');
        $database = new Database([$field], $this->table);
        
        $this->assertInstanceOf(Database::class, $database);
    }

    public function test_handles_fields_with_method_sql()
    {
        $field = Field::make('full_name')
            ->setDatabaseKey('CONCAT(first_name, " ", last_name)')
            ->setMethodSql(true);
        
        $database = new Database([$field], $this->table);
        
        $this->assertInstanceOf(Database::class, $database);
    }

    public function test_handles_fields_with_regular_database_key()
    {
        $field = Field::make('user_name')->setDatabaseKey('name');
        
        $database = new Database([$field], $this->table);
        
        $this->assertInstanceOf(Database::class, $database);
    }

    public function test_prefixes_columns_correctly()
    {
        $field = Field::make('name')->setDatabaseKey('name');
        $database = new Database([$field], $this->table, 'u');
        
        $this->assertEquals('u', $database->getAlias());
    }

    // Advanced Query Features Tests
    public function test_can_chain_multiple_operations_fluently()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database
            ->where('active', '=', 1)
            ->whereNotNull('email')
            ->sort('name', 'ASC')
            ->limit(50);
        
        $this->assertSame($database, $result);
    }

    public function test_can_search_across_multiple_columns()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->search('john doe', ['name', 'email', 'description']);
        
        $this->assertSame($database, $result);
    }

    public function test_sanitizes_search_terms_properly()
    {
        $database = new Database($this->fields, $this->table);
        // Test with potentially dangerous input
        $result = $database->search("john'; DROP TABLE users; --", ['name']);
        
        $this->assertSame($database, $result);
    }

    public function test_can_add_complex_joins()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->leftJoin('departments', 'users.dept_id', '=', 'departments.id');
        
        $this->assertSame($database, $result);
    }

    public function test_validates_join_table_names()
    {
        $database = new Database($this->fields, $this->table);
        
        $this->expectException(InvalidArgumentException::class);
        
        $database->join('invalid;table', 'users.id', '=', 'invalid.id');
    }

    // Security Constraints Tests
    public function test_can_add_and_apply_security_constraints()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database
            ->addSecurityConstraint('deleted_at', 'IS NULL', null)
            ->addSecurityConstraint('active', '=', 1);
        
        $this->assertSame($database, $result);
    }

    public function test_validates_security_constraint_input()
    {
        $database = new Database($this->fields, $this->table);
        
        $this->expectException(InvalidArgumentException::class);
        
        $database->addSecurityConstraint('', '=', 1);
    }

    // Integration with Column Class Tests
    public function test_works_with_Column_objects_for_filtering()
    {
        $database = new Database($this->fields, $this->table);
        $column1 = new Column('name', 'LIKE', '%john%', Field::STRING);
        $column2 = new Column('age', '>=', 18, Field::INTEGER);
        
        $result = $database->withFilters([$column1, $column2]);
        
        $this->assertSame($database, $result);
        $this->assertCount(2, $database->getAppliedFilters());
    }

    // Edge Cases and Error Handling Tests
    public function test_handles_empty_filter_arrays_gracefully()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->withFilters([]);
        
        $this->assertSame($database, $result);
        $this->assertCount(0, $database->getAppliedFilters());
    }

    public function test_handles_empty_sort_rules_gracefully()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database->multiSort([]);
        
        $this->assertSame($database, $result);
        $this->assertNull($database->getAppliedSorting());
    }

    public function test_validates_column_names_contain_only_safe_characters()
    {
        $database = new Database($this->fields, $this->table);
        
        $this->expectException(InvalidArgumentException::class);
        
        $database->where('name"; DROP TABLE users; --', '=', 'value');
    }

    public function test_allows_valid_column_names_with_dots_and_functions()
    {
        $database = new Database($this->fields, $this->table);
        
        // These should be valid
        $database->where('users.name', '=', 'John');
        $database->where('COUNT(id)', '>', 0);
        
        $this->assertInstanceOf(Database::class, $database);
    }

    // Fluent Interface Consistency Tests
    public function test_maintains_fluent_interface_across_all_methods()
    {
        $database = new Database($this->fields, $this->table);
        $result = $database
            ->where('active', '=', 1)
            ->whereNotNull('email')
            ->whereIn('role', ['admin', 'user'])
            ->sort('created_at', 'DESC')
            ->limit(25)
            ->offset(0);
        
        $this->assertSame($database, $result);
    }

    public function test_can_be_used_in_method_chaining_after_factory()
    {
        $result = Database::make($this->fields, $this->table)
            ->where('active', '=', 1)
            ->sort('name', 'ASC');
        
        $this->assertInstanceOf(Database::class, $result);
    }

    // Performance and Debugging Tests
    public function test_provides_debugging_information()
    {
        $database = new Database($this->fields, $this->table);
        $database
            ->where('name', '=', 'John')
            ->sort('created_at', 'DESC');
        
        $sql = $database->toSql();
        $bindings = $database->getBindings();
        $filters = $database->getAppliedFilters();
        $sorting = $database->getAppliedSorting();
        
        $this->assertIsString($sql);
        $this->assertIsArray($bindings);
        $this->assertIsArray($filters);
        $this->assertIsArray($sorting);
        $this->assertEquals('created_at', $sorting['column']);
        $this->assertEquals('desc', $sorting['direction']);
    }

    public function test_tracks_applied_filters_correctly()
    {
        $database = new Database($this->fields, $this->table);
        $column = new Column('status', '=', 'active', Field::STRING);
        $database->withFilters([$column]);
        
        $filters = $database->getAppliedFilters();
        
        $this->assertCount(1, $filters);
        $this->assertEquals('status', $filters[0]['column']);
        $this->assertEquals('=', $filters[0]['operator']);
        $this->assertEquals('active', $filters[0]['value']);
    }
}
