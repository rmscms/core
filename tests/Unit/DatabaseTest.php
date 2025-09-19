<?php

use RMS\Core\Data\Database;
use RMS\Core\Data\Field;
use RMS\Core\Data\Column;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

uses(TestCase::class);

describe('Database Class', function () {
    beforeEach(function () {
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
    });
    
    afterEach(function () {
        Schema::dropIfExists('users');
    });

    describe('Constructor and Factory Methods', function () {
        it('can be instantiated with valid parameters', function () {
            $database = new Database($this->fields, $this->table);
            
            expect($database)->toBeInstanceOf(Database::class);
            expect($database->getTable())->toBe($this->table);
            expect($database->getAlias())->toBe('a');
            expect($database->getFields())->toBe($this->fields);
        });

        it('can be created with custom alias', function () {
            $database = new Database($this->fields, $this->table, 'u');
            
            expect($database->getAlias())->toBe('u');
        });

        it('throws exception with empty table name', function () {
            expect(function () {
                new Database($this->fields, '');
            })->toThrow(InvalidArgumentException::class, 'Table name cannot be empty');
        });

        it('throws exception with invalid table name', function () {
            expect(function () {
                new Database($this->fields, 'users; DROP TABLE users;');
            })->toThrow(InvalidArgumentException::class, 'Invalid table name format');
        });

        it('throws exception with empty fields array', function () {
            expect(function () {
                new Database([], $this->table);
            })->toThrow(InvalidArgumentException::class, 'Fields array cannot be empty');
        });

        it('throws exception with invalid field types', function () {
            expect(function () {
                new Database(['invalid'], $this->table);
            })->toThrow(InvalidArgumentException::class, 'All fields must be instances of Field class');
        });

        it('can be created using static make method', function () {
            $database = Database::make($this->fields, $this->table);
            
            expect($database)->toBeInstanceOf(Database::class);
            expect($database->getTable())->toBe($this->table);
        });

        it('can be created using fromTable factory method', function () {
            $database = Database::fromTable($this->table, ['id', 'name', 'email']);
            
            expect($database)->toBeInstanceOf(Database::class);
            expect($database->getFields())->toHaveCount(3);
        });

        it('fromTable handles wildcard columns correctly', function () {
            $database = Database::fromTable($this->table, ['*']);
            
            expect($database->getFields())->toHaveCount(1);
        });
    });

    describe('Query Building', function () {
        beforeEach(function () {
            $this->database = new Database($this->fields, $this->table);
        });

        it('generates query builder correctly', function () {
            $builder = $this->database->getQueryBuilder();
            
            expect($builder)->toBeInstanceOf(Builder::class);
        });

        it('generates correct SQL string', function () {
            $sql = $this->database->toSql();
            
            expect($sql)->toContain('select');
            expect($sql)->toContain('users');
        });

        it('can add where conditions with fluent interface', function () {
            $result = $this->database->where('name', '=', 'John');
            
            expect($result)->toBe($this->database); // Check fluent interface
        });

        it('validates column names in where clauses', function () {
            expect(function () {
                $this->database->where('', '=', 'value');
            })->toThrow(InvalidArgumentException::class, 'Column name cannot be empty');
        });

        it('validates operators in where clauses', function () {
            expect(function () {
                $this->database->where('name', 'INVALID_OP', 'value');
            })->toThrow(InvalidArgumentException::class, 'Invalid SQL operator');
        });

        it('can add whereIn conditions', function () {
            $result = $this->database->whereIn('id', [1, 2, 3]);
            
            expect($result)->toBe($this->database);
        });

        it('can add whereNotIn conditions', function () {
            $result = $this->database->whereNotIn('status', ['inactive', 'deleted']);
            
            expect($result)->toBe($this->database);
        });

        it('can add whereNull conditions', function () {
            $result = $this->database->whereNull('deleted_at');
            
            expect($result)->toBe($this->database);
        });

        it('can add whereNotNull conditions', function () {
            $result = $this->database->whereNotNull('email');
            
            expect($result)->toBe($this->database);
        });

        it('can add date range filters', function () {
            $result = $this->database->whereDateBetween('created_at', '2023-01-01', '2023-12-31');
            
            expect($result)->toBe($this->database);
        });

        it('can add search across multiple columns', function () {
            $result = $this->database->search('john', ['name', 'email']);
            
            expect($result)->toBe($this->database);
        });

        it('ignores empty search terms', function () {
            $result = $this->database->search('', ['name', 'email']);
            
            expect($result)->toBe($this->database);
        });
    });

    describe('Filtering', function () {
        beforeEach(function () {
            $this->database = new Database($this->fields, $this->table);
        });

        it('can apply single filter using Column object', function () {
            $column = new Column('name', '=', 'John', Field::STRING);
            
            $result = $this->database->withFilters([$column]);
            
            expect($result)->toBe($this->database);
            expect($this->database->getAppliedFilters())->toHaveCount(1);
        });

        it('can apply multiple filters', function () {
            $filters = [
                new Column('name', '=', 'John', Field::STRING),
                new Column('age', '>', 18, Field::INTEGER)
            ];
            
            $this->database->withFilters($filters);
            
            expect($this->database->getAppliedFilters())->toHaveCount(2);
        });

        it('validates filter column names', function () {
            $invalidFilter = new Column('', '=', 'value', Field::STRING);
            
            expect(function () {
                $this->database->withFilters([$invalidFilter]);
            })->toThrow(ErrorException::class);
        });

        it('validates filter operators', function () {
            expect(function () {
                new Column('name', 'INVALID', 'value', Field::STRING);
            })->toThrow(InvalidArgumentException::class, 'Invalid operator: INVALID');
        });

        it('validates IN/NOT IN operators require array values', function () {
            $invalidFilter = new Column('id', 'IN', 'not_array', Field::INTEGER);
            $database = $this->database;
            
            expect(function () use ($database, $invalidFilter) {
                $database->withFilters([$invalidFilter]);
            })->toThrow(InvalidArgumentException::class, 'IN/NOT IN operators require array values');
        });
    });

    describe('Sorting', function () {
        beforeEach(function () {
            $this->database = new Database($this->fields, $this->table);
        });

        it('can apply single sort with fluent interface', function () {
            $result = $this->database->sort('name', 'ASC');
            
            expect($result)->toBe($this->database);
            
            $appliedSorting = $this->database->getAppliedSorting();
            expect($appliedSorting['column'])->toBe('name');
            expect($appliedSorting['direction'])->toBe('asc');
        });

        it('validates sort direction', function () {
            expect(function () {
                $this->database->sort('name', 'INVALID');
            })->toThrow(InvalidArgumentException::class, 'Sort direction must be ASC or DESC');
        });

        it('can apply multiple sorts', function () {
            $sortRules = [
                ['name', 'ASC'],
                ['created_at', 'DESC']
            ];
            
            $result = $this->database->multiSort($sortRules);
            
            expect($result)->toBe($this->database);
        });

        it('handles invalid sort rules gracefully', function () {
            $sortRules = [
                ['name', 'ASC'],
                'invalid_rule', // This should be ignored
                ['email', 'DESC']
            ];
            
            $result = $this->database->multiSort($sortRules);
            
            expect($result)->toBe($this->database);
        });
    });

    describe('Query Modifiers', function () {
        beforeEach(function () {
            $this->database = new Database($this->fields, $this->table);
        });

        it('can add join with fluent interface', function () {
            $result = $this->database->join('profiles', 'users.id', '=', 'profiles.user_id');
            
            expect($result)->toBe($this->database);
        });

        it('can add left join', function () {
            $result = $this->database->leftJoin('profiles', 'users.id', '=', 'profiles.user_id');
            
            expect($result)->toBe($this->database);
        });

        it('can add group by with single column', function () {
            $result = $this->database->groupBy('department');
            
            expect($result)->toBe($this->database);
        });

        it('can add group by with multiple columns', function () {
            $result = $this->database->groupBy(['department', 'role']);
            
            expect($result)->toBe($this->database);
        });

        it('can add having condition', function () {
            $result = $this->database->having('COUNT(*)', '>', 5);
            
            expect($result)->toBe($this->database);
        });

        it('can set limit', function () {
            $result = $this->database->limit(10);
            
            expect($result)->toBe($this->database);
        });

        it('validates limit is positive', function () {
            expect(function () {
                $this->database->limit(0);
            })->toThrow(InvalidArgumentException::class, 'Limit must be greater than 0');
        });

        it('can set offset', function () {
            $result = $this->database->offset(20);
            
            expect($result)->toBe($this->database);
        });

        it('validates offset is non-negative', function () {
            expect(function () {
                $this->database->offset(-1);
            })->toThrow(InvalidArgumentException::class, 'Offset must be >= 0');
        });
    });

    describe('Security Features', function () {
        beforeEach(function () {
            $this->database = new Database($this->fields, $this->table);
        });

        it('can add security constraints', function () {
            $result = $this->database->addSecurityConstraint('active', '=', 1);
            
            expect($result)->toBe($this->database);
        });

        it('validates security constraint columns', function () {
            expect(function () {
                $this->database->addSecurityConstraint('', '=', 1);
            })->toThrow(InvalidArgumentException::class);
        });

        it('validates security constraint operators', function () {
            expect(function () {
                $this->database->addSecurityConstraint('active', 'INVALID', 1);
            })->toThrow(InvalidArgumentException::class);
        });

        it('sanitizes table names', function () {
            $database = new Database($this->fields, 'users_table_123');
            
            expect($database->getTable())->toBe('users_table_123');
        });
    });

    describe('Data Retrieval', function () {
        beforeEach(function () {
            $this->database = new Database($this->fields, $this->table);
        });

        it('validates per page parameter', function () {
            expect(function () {
                $this->database->get(0);
            })->toThrow(InvalidArgumentException::class, 'Per page must be between 1 and 1000');
        });

        it('validates per page upper limit', function () {
            expect(function () {
                $this->database->get(1001);
            })->toThrow(InvalidArgumentException::class, 'Per page must be between 1 and 1000');
        });

        it('has getter methods for debugging', function () {
            $this->database->where('name', '=', 'John');
            
            $sql = $this->database->toSql();
            $bindings = $this->database->getBindings();
            
            expect($sql)->toBeString();
            expect($bindings)->toBeArray();
        });
    });

    describe('Field SQL Generation', function () {
        it('handles fields without database_key', function () {
            $field = Field::make('test_field');
            $database = new Database([$field], $this->table);
            
            expect($database)->toBeInstanceOf(Database::class);
        });

        it('handles fields with method_sql', function () {
            $field = Field::make('full_name')
                ->setDatabaseKey('CONCAT(first_name, " ", last_name)')
                ->setMethodSql(true);
            
            $database = new Database([$field], $this->table);
            
            expect($database)->toBeInstanceOf(Database::class);
        });

        it('handles fields with regular database_key', function () {
            $field = Field::make('user_name')->setDatabaseKey('name');
            
            $database = new Database([$field], $this->table);
            
            expect($database)->toBeInstanceOf(Database::class);
        });

        it('prefixes columns correctly', function () {
            $field = Field::make('name')->setDatabaseKey('name');
            $database = new Database([$field], $this->table, 'u');
            
            expect($database->getAlias())->toBe('u');
        });
    });

    describe('Advanced Query Features', function () {
        beforeEach(function () {
            $this->database = new Database($this->fields, $this->table);
        });

        it('can chain multiple operations fluently', function () {
            $result = $this->database
                ->where('active', '=', 1)
                ->whereNotNull('email')
                ->sort('name', 'ASC')
                ->limit(50);
            
            expect($result)->toBe($this->database);
        });

        it('can search across multiple columns', function () {
            $result = $this->database->search('john doe', ['name', 'email', 'description']);
            
            expect($result)->toBe($this->database);
        });

        it('sanitizes search terms properly', function () {
            // Test with potentially dangerous input
            $result = $this->database->search("john'; DROP TABLE users; --", ['name']);
            
            expect($result)->toBe($this->database);
        });

        it('can add complex joins', function () {
            $result = $this->database
                ->join('profiles', 'users.id', '=', 'profiles.user_id')
                ->leftJoin('departments', 'users.dept_id', '=', 'departments.id');
            
            expect($result)->toBe($this->database);
        });

        it('validates join table names', function () {
            expect(function () {
                $this->database->join('invalid;table', 'users.id', '=', 'invalid.id');
            })->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Security Constraints', function () {
        beforeEach(function () {
            $this->database = new Database($this->fields, $this->table);
        });

        it('can add and apply security constraints', function () {
            $result = $this->database
                ->addSecurityConstraint('deleted_at', 'IS NULL', null)
                ->addSecurityConstraint('active', '=', 1);
            
            expect($result)->toBe($this->database);
        });

        it('validates security constraint input', function () {
            expect(function () {
                $this->database->addSecurityConstraint('', '=', 1);
            })->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Integration with Column Class', function () {
        beforeEach(function () {
            $this->database = new Database($this->fields, $this->table);
        });

        it('works with Column objects for filtering', function () {
            $column1 = new Column('name', 'LIKE', '%john%', Field::STRING);
            $column2 = new Column('age', '>=', 18, Field::INTEGER);
            
            $result = $this->database->withFilters([$column1, $column2]);
            
            expect($result)->toBe($this->database);
            expect($this->database->getAppliedFilters())->toHaveCount(2);
        });

        it('validates Column object properties', function () {
            $invalidColumn = new Column('', '=', 'value', Field::STRING);
            
            expect(function () {
                $this->database->withFilters([$invalidColumn]);
            })->toThrow(ErrorException::class);
        });
    });

    describe('Edge Cases and Error Handling', function () {
        beforeEach(function () {
            $this->database = new Database($this->fields, $this->table);
        });

        it('handles empty filter arrays gracefully', function () {
            $result = $this->database->withFilters([]);
            
            expect($result)->toBe($this->database);
            expect($this->database->getAppliedFilters())->toHaveCount(0);
        });

        it('handles empty sort rules gracefully', function () {
            $result = $this->database->multiSort([]);
            
            expect($result)->toBe($this->database);
            expect($this->database->getAppliedSorting())->toBeNull();
        });

        it('validates column names contain only safe characters', function () {
            expect(function () {
                $this->database->where('name"; DROP TABLE users; --', '=', 'value');
            })->toThrow(InvalidArgumentException::class);
        });

        it('allows valid column names with dots and functions', function () {
            // These should be valid
            $this->database->where('users.name', '=', 'John');
            $this->database->where('COUNT(id)', '>', 0);
            
            expect($this->database)->toBeInstanceOf(Database::class);
        });
    });

    describe('Fluent Interface Consistency', function () {
        beforeEach(function () {
            $this->database = new Database($this->fields, $this->table);
        });

        it('maintains fluent interface across all methods', function () {
            $result = $this->database
                ->where('active', '=', 1)
                ->whereNotNull('email')
                ->whereIn('role', ['admin', 'user'])
                ->sort('created_at', 'DESC')
                ->limit(25)
                ->offset(0);
            
            expect($result)->toBe($this->database);
        });

        it('can be used in method chaining after factory', function () {
            $result = Database::make($this->fields, $this->table)
                ->where('active', '=', 1)
                ->sort('name', 'ASC');
            
            expect($result)->toBeInstanceOf(Database::class);
        });
    });

    describe('Performance and Debugging', function () {
        beforeEach(function () {
            $this->database = new Database($this->fields, $this->table);
        });

        it('provides debugging information', function () {
            $this->database
                ->where('name', '=', 'John')
                ->sort('created_at', 'DESC');
            
            $sql = $this->database->toSql();
            $bindings = $this->database->getBindings();
            $filters = $this->database->getAppliedFilters();
            $sorting = $this->database->getAppliedSorting();
            
            expect($sql)->toBeString();
            expect($bindings)->toBeArray();
            expect($filters)->toBeArray();
            expect($sorting)->toBeArray();
            expect($sorting['column'])->toBe('created_at');
            expect($sorting['direction'])->toBe('desc');
        });

        it('tracks applied filters correctly', function () {
            $column = new Column('status', '=', 'active', Field::STRING);
            $this->database->withFilters([$column]);
            
            $filters = $this->database->getAppliedFilters();
            
            expect($filters)->toHaveCount(1);
            expect($filters[0]['column'])->toBe('status');
            expect($filters[0]['operator'])->toBe('=');
            expect($filters[0]['value'])->toBe('active');
        });
    });
});
