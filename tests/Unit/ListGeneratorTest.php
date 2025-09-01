<?php

use RMS\Core\Data\ListGenerator;
use RMS\Core\Data\Field;
use RMS\Core\Data\Column;
use RMS\Core\Data\Link;
use RMS\Core\Data\ListResponse;
use RMS\Core\Data\Database;
use RMS\Core\Contracts\List\HasList;
use RMS\Core\Contracts\Data\UseDatabase;
use RMS\Core\Contracts\Filter\HasSort;
use RMS\Core\Contracts\Filter\ShouldFilter;

describe('ListGenerator Class', function () {
    beforeEach(function () {
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

            public function routeParameter(): string
            {
                return 'user';
            }

            public function baseRoute(): string
            {
                return 'admin.users';
            }

            public function getPerPage(): int
            {
                return 25;
            }
            
            public function setTplList(): void
            {
                // Mock implementation
            }
            
            public function getListConfig(): array
            {
                return ['showPagination' => true, 'showSearch' => false];
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

            public function routeParameter(): string
            {
                return 'user';
            }

            public function baseRoute(): string
            {
                return 'admin.users';
            }

            public function getPerPage(): int
            {
                return 25;
            }
            
            public function setTplList(): void
            {
                // Mock implementation
            }
            
            public function getListConfig(): array
            {
                return ['showPagination' => true, 'showSearch' => true];
            }

            public function table(): string
            {
                return 'users';
            }

            public function query($query): void
            {
                // Custom query modifications
            }
            
            public function model($id = null)
            {
                return (object) ['id' => $id, 'name' => 'John Doe', 'email' => 'john@example.com'];
            }
            
            public function modelName(): string
            {
                return 'App\\Models\\User';
            }
        };

        // Mock list with filtering and sorting
        $this->mockComplexList = new class implements HasList, UseDatabase, ShouldFilter, HasSort {
            public function getListFields(): array
            {
                return [
                    Field::make('id')->setDatabaseKey('id'),
                    Field::make('name')->setDatabaseKey('name'),
                    Field::make('email')->setDatabaseKey('email')
                ];
            }

            public function routeParameter(): string
            {
                return 'user';
            }

            public function baseRoute(): string
            {
                return 'admin.users';
            }

            public function getPerPage(): int
            {
                return 25;
            }
            
            public function setTplList(): void
            {
                // Mock implementation
            }
            
            public function getListConfig(): array
            {
                return ['showPagination' => true, 'showSearch' => true, 'showFilters' => true];
            }

            public function table(): string
            {
                return 'users';
            }

            public function query($query): void
            {
                // Custom query modifications
            }
            
            public function model($id = null)
            {
                return (object) ['id' => $id, 'name' => 'John Doe', 'email' => 'john@example.com'];
            }
            
            public function modelName(): string
            {
                return 'App\\Models\\User';
            }

            public function getFilters(): array
            {
                return [
                    new Column('active', '=', 1),
                    new Column('deleted_at', 'IS NULL', null)
                ];
            }

            public function orderBy(): string
            {
                return 'name';
            }

            public function orderWay(): string
            {
                return 'ASC';
            }

            public function getSearchableColumns(): array
            {
                return ['name', 'email', 'description'];
            }

            public function getSecurityConstraints(): array
            {
                return [
                    ['column' => 'tenant_id', 'operator' => '=', 'value' => 1]
                ];
            }
        };
    });

    describe('Constructor and Factory Methods', function () {
        it('can be instantiated with valid list', function () {
            $generator = new ListGenerator($this->mockList);
            
            expect($generator)->toBeInstanceOf(ListGenerator::class);
            expect($generator->getList())->toBe($this->mockList);
            expect($generator->getFields())->toHaveCount(3);
            expect($generator->getBaseRoute())->toBe('admin.users');
            expect($generator->getRouteParameter())->toBe('user');
        });

        it('can be created with static make method', function () {
            $generator = ListGenerator::make($this->mockList);
            
            expect($generator)->toBeInstanceOf(ListGenerator::class);
            expect($generator->getList())->toBe($this->mockList);
        });

        it('validates field types in constructor', function () {
            $invalidList = new class implements HasList {
                public function getListFields(): array
                {
                    return ['invalid_field']; // Not a Field instance
                }

                public function routeParameter(): string { return 'test'; }
                public function baseRoute(): string { return 'test'; }
                public function getPerPage(): int { return 20; }
                public function setTplList(): void { }
                public function getListConfig(): array { return []; }
            };

            expect(function () {
                new ListGenerator($invalidList);
            })->toThrow(InvalidArgumentException::class, 'All fields must be instances of Field class');
        });

        it('initializes database for UseDatabase lists', function () {
            $generator = new ListGenerator($this->mockDatabaseList);
            
            expect($generator->getDatabase())->toBeInstanceOf(Database::class);
        });

        it('does not initialize database for regular lists', function () {
            $generator = new ListGenerator($this->mockList);
            
            expect($generator->getDatabase())->toBeNull();
        });
    });

    describe('List Generation', function () {
        it('generates list response for database lists', function () {
            $generator = new ListGenerator($this->mockDatabaseList);
            $response = $generator->generate();
            
            expect($response)->toBeInstanceOf(ListResponse::class);
        });

        it('throws exception for non-database lists', function () {
            $generator = new ListGenerator($this->mockList);
            
            expect(function () {
                $generator->generate();
            })->toThrow(\Exception::class, 'This class should implement: ' . UseDatabase::class);
        });

        it('uses enhanced database building for complex lists', function () {
            $generator = new ListGenerator($this->mockComplexList);
            $database = $generator->builder();
            
            expect($database)->toBeInstanceOf(Database::class);
            expect($database->getAppliedFilters())->toHaveCount(2); // From getFilters()
            expect($database->getAppliedSorting())->toBeArray();
        });
    });

    describe('Fluent Interface Methods', function () {
        beforeEach(function () {
            $this->generator = new ListGenerator($this->mockDatabaseList);
        });

        it('can set search term with fluent interface', function () {
            $result = $this->generator->search('john doe');
            
            expect($result)->toBe($this->generator);
            expect($this->generator->getSearchTerm())->toBe('john doe');
        });

        it('trims search terms', function () {
            $this->generator->search('  search term  ');
            
            expect($this->generator->getSearchTerm())->toBe('search term');
        });

        it('can add single filter', function () {
            $filter = new Column('status', '=', 'active');
            $result = $this->generator->addFilter($filter);
            
            expect($result)->toBe($this->generator);
            expect($this->generator->getCustomFilters())->toHaveCount(1);
        });

        it('can add multiple filters', function () {
            $filters = [
                new Column('status', '=', 'active'),
                new Column('role', '!=', 'guest')
            ];
            
            $result = $this->generator->addFilters($filters);
            
            expect($result)->toBe($this->generator);
            expect($this->generator->getCustomFilters())->toHaveCount(2);
        });

        it('ignores invalid filters when adding multiple', function () {
            $filters = [
                new Column('status', '=', 'active'),
                'invalid_filter', // This should be ignored
                new Column('role', '!=', 'guest')
            ];
            
            $result = $this->generator->addFilters($filters);
            
            expect($result)->toBe($this->generator);
            expect($this->generator->getCustomFilters())->toHaveCount(2);
        });

        it('can set custom per page value', function () {
            $result = $this->generator->setPerPage(50);
            
            expect($result)->toBe($this->generator);
            expect($this->generator->per_page)->toBe(50);
        });

        it('validates per page bounds', function () {
            expect(function () {
                $this->generator->setPerPage(0);
            })->toThrow(InvalidArgumentException::class, 'Per page must be between 1 and 1000');

            expect(function () {
                $this->generator->setPerPage(1001);
            })->toThrow(InvalidArgumentException::class, 'Per page must be between 1 and 1000');
        });

        it('can enable/disable simple pagination', function () {
            $result = $this->generator->useSimplePagination(true);
            
            expect($result)->toBe($this->generator);
            expect($this->generator->simple_pagination)->toBeTrue();
        });

        it('can toggle create button', function () {
            $result = $this->generator->showCreateButton(false);
            
            expect($result)->toBe($this->generator);
            expect($this->generator->hasCreateButton())->toBeFalse();
        });

        it('can toggle batch destroy', function () {
            $result = $this->generator->enableBatchDestroy(false);
            
            expect($result)->toBe($this->generator);
            expect($this->generator->hasBatchDestroy())->toBeFalse();
        });

        it('can toggle batch active', function () {
            $result = $this->generator->enableBatchActive(true);
            
            expect($result)->toBe($this->generator);
            expect($this->generator->hasBatchActive())->toBeTrue();
        });

        it('can set identifier', function () {
            $result = $this->generator->setIdentifier('uuid');
            
            expect($result)->toBe($this->generator);
            expect($this->generator->identifier)->toBe('uuid');
        });

        it('can add links', function () {
            $link = new Link('Export', '/export', 'secondary');
            $result = $this->generator->link($link);
            
            expect($result)->toBe($this->generator);
            expect($this->generator->getLinks())->toHaveCount(1);
        });
    });

    describe('Query Building', function () {
        it('builds basic database query', function () {
            $generator = new ListGenerator($this->mockDatabaseList);
            $database = $generator->builder();
            
            expect($database)->toBeInstanceOf(Database::class);
        });

        it('applies filters from list implementation', function () {
            $generator = new ListGenerator($this->mockComplexList);
            $database = $generator->buildQuery();
            
            expect($database->getAppliedFilters())->toHaveCount(2);
        });

        it('applies sorting from list implementation', function () {
            $generator = new ListGenerator($this->mockComplexList);
            $database = $generator->buildQuery();
            
            $sorting = $database->getAppliedSorting();
            expect($sorting['column'])->toBe('name');
            expect($sorting['direction'])->toBe('asc');
        });

        it('applies custom filters in addition to list filters', function () {
            $generator = new ListGenerator($this->mockComplexList);
            $generator->addFilter(new Column('department', '=', 'IT'));
            
            $database = $generator->buildQuery();
            
            // Should have list filters + custom filter
            expect($database->getAppliedFilters())->toHaveCount(3);
        });

        it('applies search when term is set and list supports it', function () {
            $generator = new ListGenerator($this->mockComplexList);
            $generator->search('john doe');
            
            $database = $generator->buildQuery();
            
            expect($database)->toBeInstanceOf(Database::class);
        });

        it('ignores search when list does not support it', function () {
            $generator = new ListGenerator($this->mockDatabaseList);
            $generator->search('john doe');
            
            $database = $generator->buildQuery();
            
            expect($database)->toBeInstanceOf(Database::class);
        });
    });

    describe('Per Page Calculation', function () {
        it('uses list per page value when available', function () {
            $generator = new ListGenerator($this->mockDatabaseList);
            
            expect($generator->perPage())->toBe(25); // From mockDatabaseList
        });

        it('falls back to default when list per page is 0', function () {
            $listWithZeroPerPage = new class implements HasList, UseDatabase {
                public function getListFields(): array
                {
                    return [Field::make('id')];
                }
                public function routeParameter(): string { return 'test'; }
                public function baseRoute(): string { return 'test'; }
                public function getPerPage(): int { return 0; }
                public function setTplList(): void { }
                public function getListConfig(): array { return []; }
                public function table(): string { return 'test'; }
                public function query($query): void { }
                public function model($id = null) { return (object) ['id' => $id]; }
                public function modelName(): string { return 'App\\Models\\Test'; }
            };

            $generator = new ListGenerator($listWithZeroPerPage);
            
            expect($generator->perPage())->toBe(20); // Default value
        });

        it('can override per page with custom value', function () {
            $generator = new ListGenerator($this->mockDatabaseList);
            $generator->setPerPage(100);
            
            expect($generator->perPage())->toBe(25); // Still uses list value
            expect($generator->per_page)->toBe(100); // But property is updated
        });
    });

    describe('Method Chaining and Fluent Interface', function () {
        it('supports full fluent interface chaining', function () {
            $filter = new Column('status', '=', 'active');
            $link = new Link('Export', '/export', 'secondary');
            
            $result = ListGenerator::make($this->mockDatabaseList)
                ->search('john')
                ->addFilter($filter)
                ->setPerPage(50)
                ->useSimplePagination(true)
                ->showCreateButton(false)
                ->enableBatchDestroy(false)
                ->enableBatchActive(true)
                ->setIdentifier('uuid')
                ->link($link);
            
            expect($result)->toBeInstanceOf(ListGenerator::class);
            expect($result->getSearchTerm())->toBe('john');
            expect($result->getCustomFilters())->toHaveCount(1);
            expect($result->per_page)->toBe(50);
            expect($result->simple_pagination)->toBeTrue();
            expect($result->hasCreateButton())->toBeFalse();
            expect($result->hasBatchDestroy())->toBeFalse();
            expect($result->hasBatchActive())->toBeTrue();
            expect($result->identifier)->toBe('uuid');
            expect($result->getLinks())->toHaveCount(1);
        });
    });

    describe('Security Features', function () {
        it('applies security constraints from list', function () {
            $generator = new ListGenerator($this->mockComplexList);
            
            expect($generator->getDatabase())->toBeInstanceOf(Database::class);
        });

        it('handles lists without security constraints', function () {
            $generator = new ListGenerator($this->mockDatabaseList);
            
            expect($generator->getDatabase())->toBeInstanceOf(Database::class);
        });
    });

    describe('Error Handling and Edge Cases', function () {
        it('handles database initialization errors gracefully', function () {
            $problematicList = new class implements HasList, UseDatabase {
                public function getListFields(): array
                {
                    return [Field::make('id')];
                }
                public function routeParameter(): string { return 'test'; }
                public function baseRoute(): string { return 'test'; }
                public function getPerPage(): int { return 20; }
                public function setTplList(): void { }
                public function getListConfig(): array { return []; }
                public function query($query): void { }
                public function model($id = null) { return (object) ['id' => $id]; }
                public function modelName(): string { return 'App\\Models\\Test'; }

                public function table(): string
                {
                    throw new \Exception('Database error');
                }
            };

            $generator = new ListGenerator($problematicList);
            
            expect($generator->getDatabase())->toBeNull();
        });

        it('handles empty custom filters', function () {
            $generator = new ListGenerator($this->mockDatabaseList);
            $database = $generator->buildQuery();
            
            expect($database)->toBeInstanceOf(Database::class);
            expect($generator->getCustomFilters())->toHaveCount(0);
        });

        it('handles empty search term', function () {
            $generator = new ListGenerator($this->mockComplexList);
            $generator->search('');
            
            $database = $generator->buildQuery();
            
            expect($database)->toBeInstanceOf(Database::class);
        });

        it('handles lists without query method', function () {
            $listWithoutQuery = new class implements HasList, UseDatabase {
                public function getListFields(): array
                {
                    return [Field::make('id')];
                }
                public function routeParameter(): string { return 'test'; }
                public function baseRoute(): string { return 'test'; }
                public function getPerPage(): int { return 20; }
                public function setTplList(): void { }
                public function getListConfig(): array { return []; }
                public function model($id = null) { return (object) ['id' => $id]; }
                public function modelName(): string { return 'App\\Models\\Test'; }
                public function table(): string { return 'test_table'; }
            };

            $generator = new ListGenerator($listWithoutQuery);
            $database = $generator->buildQuery();
            
            expect($database)->toBeInstanceOf(Database::class);
        });
    });

    describe('Advanced Query Features', function () {
        beforeEach(function () {
            $this->generator = new ListGenerator($this->mockComplexList);
        });

        it('combines all query features correctly', function () {
            $customFilter = new Column('department', '=', 'IT');
            
            $this->generator
                ->search('john')
                ->addFilter($customFilter);
            
            $database = $this->generator->buildQuery();
            
            expect($database)->toBeInstanceOf(Database::class);
            // Should have list filters + custom filter
            expect($database->getAppliedFilters())->toHaveCount(3);
            expect($database->getAppliedSorting())->toBeArray();
        });

        it('maintains builder compatibility', function () {
            $database1 = $this->generator->builder();
            $database2 = $this->generator->buildQuery();
            
            expect($database1)->toBeInstanceOf(Database::class);
            expect($database2)->toBeInstanceOf(Database::class);
        });
    });

    describe('State Management', function () {
        beforeEach(function () {
            $this->generator = new ListGenerator($this->mockDatabaseList);
        });

        it('maintains state across multiple operations', function () {
            $this->generator
                ->search('test search')
                ->setPerPage(100)
                ->showCreateButton(false);
            
            expect($this->generator->getSearchTerm())->toBe('test search');
            expect($this->generator->per_page)->toBe(100);
            expect($this->generator->hasCreateButton())->toBeFalse();
            
            // Add more operations
            $this->generator
                ->enableBatchActive(true)
                ->setIdentifier('custom_id');
            
            // Previous state should be preserved
            expect($this->generator->getSearchTerm())->toBe('test search');
            expect($this->generator->per_page)->toBe(100);
            expect($this->generator->hasCreateButton())->toBeFalse();
            // New state should be applied
            expect($this->generator->hasBatchActive())->toBeTrue();
            expect($this->generator->identifier)->toBe('custom_id');
        });
    });

    describe('Getter Methods', function () {
        beforeEach(function () {
            $this->generator = ListGenerator::make($this->mockComplexList)
                ->search('test')
                ->setPerPage(75);
        });

        it('returns correct list instance', function () {
            expect($this->generator->getList())->toBe($this->mockComplexList);
        });

        it('returns correct fields', function () {
            expect($this->generator->getFields())->toHaveCount(3);
        });

        it('returns correct search term', function () {
            expect($this->generator->getSearchTerm())->toBe('test');
        });

        it('returns correct base route and parameter', function () {
            expect($this->generator->getBaseRoute())->toBe('admin.users');
            expect($this->generator->getRouteParameter())->toBe('user');
        });

        it('returns correct feature flags', function () {
            expect($this->generator->hasCreateButton())->toBeTrue();
            expect($this->generator->hasBatchDestroy())->toBeTrue();
            expect($this->generator->hasBatchActive())->toBeFalse();
        });

        it('returns empty links by default', function () {
            expect($this->generator->getLinks())->toHaveCount(0);
        });
    });

    describe('Complex Integration Scenarios', function () {
        it('works with all features combined', function () {
            $generator = ListGenerator::make($this->mockComplexList)
                ->search('john')
                ->addFilter(new Column('department', '=', 'IT'))
                ->setPerPage(30)
                ->useSimplePagination(false)
                ->showCreateButton(true)
                ->enableBatchDestroy(true)
                ->enableBatchActive(true);
            
            $database = $generator->buildQuery();
            $response = $generator->generate();
            
            expect($database)->toBeInstanceOf(Database::class);
            expect($response)->toBeInstanceOf(ListResponse::class);
            
            // Verify all features are applied
            expect($database->getAppliedFilters())->toHaveCount(3); // 2 from list + 1 custom
            expect($database->getAppliedSorting())->toBeArray();
            expect($generator->getSearchTerm())->toBe('john');
            expect($generator->per_page)->toBe(30);
        });
    });
});
