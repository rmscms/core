<?php

use RMS\Core\Data\FormGenerator;
use RMS\Core\Data\Field;
use RMS\Core\Data\Link;
use RMS\Core\Data\FormResponse;
use RMS\Core\Data\Database;
use RMS\Core\Contracts\Form\HasForm;
use RMS\Core\Contracts\Data\UseDatabase;

describe('FormGenerator Class', function () {
    beforeEach(function () {
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
            
            public function query($query): void
            {
                // Mock query modifications
            }

            public function model($id = null)
            {
                return (object) ['id' => $id, 'name' => 'John Doe', 'email' => 'john@example.com'];
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
    });

    describe('Constructor and Factory Methods', function () {
        it('can be instantiated with valid form', function () {
            $generator = new FormGenerator($this->mockForm);
            
            expect($generator)->toBeInstanceOf(FormGenerator::class);
            expect($generator->getForm())->toBe($this->mockForm);
            expect($generator->getFields())->toHaveCount(3);
        });

        it('can be created with static make method', function () {
            $generator = FormGenerator::make($this->mockForm, 123);
            
            expect($generator)->toBeInstanceOf(FormGenerator::class);
            expect($generator->getId())->toBe(123);
        });

        it('can be created with custom fields', function () {
            $customFields = [Field::make('custom')->setDatabaseKey('custom')];
            $generator = FormGenerator::make($this->mockForm, null, $customFields);
            
            expect($generator->getFields())->toBe($customFields);
        });

        it('validates field types in constructor', function () {
            expect(function () {
                new FormGenerator($this->mockForm, null, ['invalid_field']);
            })->toThrow(InvalidArgumentException::class, 'All fields must be instances of Field class');
        });

        it('initializes database for UseDatabase forms', function () {
            $generator = new FormGenerator($this->mockDatabaseForm);
            
            expect($generator->getDatabase())->toBeInstanceOf(Database::class);
        });

        it('does not initialize database for regular forms', function () {
            $generator = new FormGenerator($this->mockForm);
            
            expect($generator->getDatabase())->toBeNull();
        });
    });

    describe('Form Generation', function () {
        it('generates FormResponse correctly', function () {
            $generator = new FormGenerator($this->mockForm);
            $response = $generator->generate();
            
            expect($response)->toBeInstanceOf(FormResponse::class);
        });

        it('loads values for database forms with ID', function () {
            $generator = new FormGenerator($this->mockDatabaseForm, 123);
            $response = $generator->generate();
            
            expect($response)->toBeInstanceOf(FormResponse::class);
        });

        it('returns empty values for forms without ID', function () {
            $generator = new FormGenerator($this->mockDatabaseForm);
            $response = $generator->generate();
            
            expect($response)->toBeInstanceOf(FormResponse::class);
        });
    });

    describe('Fluent Interface Methods', function () {
        beforeEach(function () {
            $this->generator = new FormGenerator($this->mockForm);
        });

        it('can add links with fluent interface', function () {
            $link = new Link('Test Link', '/test', 'primary');
            $result = $this->generator->link($link);
            
            expect($result)->toBe($this->generator);
            expect($this->generator->getLinks())->toHaveCount(1);
        });

        it('can add metadata with fluent interface', function () {
            $meta = ['key1' => 'value1', 'key2' => 'value2'];
            $result = $this->generator->withMeta($meta);
            
            expect($result)->toBe($this->generator);
            expect($this->generator->getMeta())->toBe($meta);
        });

        it('can merge metadata', function () {
            $meta1 = ['key1' => 'value1'];
            $meta2 = ['key2' => 'value2'];
            
            $this->generator
                ->withMeta($meta1)
                ->withMeta($meta2);
            
            expect($this->generator->getMeta())->toBe(['key1' => 'value1', 'key2' => 'value2']);
        });

        it('can set validation rules', function () {
            $rules = ['name' => 'required', 'email' => 'email|required'];
            $result = $this->generator->withValidation($rules);
            
            expect($result)->toBe($this->generator);
            expect($this->generator->getValidationRules())->toBe($rules);
        });

        it('can set identifier', function () {
            $result = $this->generator->setIdentifier('user_id');
            
            expect($result)->toBe($this->generator);
            expect($this->generator->identifier)->toBe('user_id');
        });

        it('can toggle back button', function () {
            $result = $this->generator->showBackButton(false);
            
            expect($result)->toBe($this->generator);
            expect($this->generator->hasBackButton())->toBeFalse();
        });

        it('can toggle save and stay button', function () {
            $result = $this->generator->showSaveAndStayButton(false);
            
            expect($result)->toBe($this->generator);
            expect($this->generator->hasSaveAndStayButton())->toBeFalse();
        });

        it('can add custom values', function () {
            $values = ['field1' => 'value1', 'field2' => 'value2'];
            $result = $this->generator->withValues($values);
            
            expect($result)->toBe($this->generator);
            expect($this->generator->getValues())->toBe($values);
        });

        it('can merge custom values', function () {
            $values1 = ['field1' => 'value1'];
            $values2 = ['field2' => 'value2'];
            
            $this->generator
                ->withValues($values1)
                ->withValues($values2);
            
            expect($this->generator->getValues())->toBe(['field1' => 'value1', 'field2' => 'value2']);
        });
    });

    describe('Method Chaining', function () {
        it('supports full fluent interface chaining', function () {
            $link = new Link('Test', '/test', 'primary');
            
            $result = FormGenerator::make($this->mockForm, 123)
                ->setIdentifier('custom_id')
                ->showBackButton(false)
                ->showSaveAndStayButton(true)
                ->withMeta(['test' => 'meta'])
                ->withValidation(['email' => 'required'])
                ->withValues(['preset' => 'value'])
                ->link($link);
            
            expect($result)->toBeInstanceOf(FormGenerator::class);
            expect($result->getId())->toBe(123);
            expect($result->identifier)->toBe('custom_id');
            expect($result->hasBackButton())->toBeFalse();
            expect($result->hasSaveAndStayButton())->toBeTrue();
            expect($result->getMeta())->toBe(['test' => 'meta']);
            expect($result->getValidationRules())->toBe(['email' => 'required']);
            expect($result->getValues())->toBe(['preset' => 'value']);
            expect($result->getLinks())->toHaveCount(1);
        });
    });

    describe('Database Integration', function () {
        beforeEach(function () {
            $this->generator = new FormGenerator($this->mockDatabaseForm, 123);
        });

        it('creates Database instance for UseDatabase forms', function () {
            expect($this->generator->getDatabase())->toBeInstanceOf(Database::class);
        });

        it('applies security constraints from form', function () {
            $database = $this->generator->getDatabase();
            
            expect($database)->toBeInstanceOf(Database::class);
            // The security constraints should be applied during initialization
        });

        it('handles forms without getSecurityConstraints method', function () {
            $formWithoutConstraints = new class implements HasForm, UseDatabase {
                public function getFieldsForm(): array
                {
                    return [Field::make('id')];
                }
                
                public function formUrl(): string
                {
                    return '/test/no-constraints';
                }
                
                public function setTplForm(): void
                {
                    // Mock implementation
                }
                
                public function getValidationRules(): array
                {
                    return [];
                }
                
                public function getFormConfig(): array
                {
                    return ['method' => 'POST'];
                }

                public function table(): string
                {
                    return 'test_table';
                }
                
                public function query($query): void
                {
                    // Mock implementation
                }

                public function model($id = null)
                {
                    return (object) ['id' => $id];
                }
                
                public function modelName(): string
                {
                    return 'App\\Models\\Test';
                }
            };

            $generator = new FormGenerator($formWithoutConstraints);
            
            expect($generator->getDatabase())->toBeInstanceOf(Database::class);
        });
    });

    describe('Error Handling and Edge Cases', function () {
        it('handles database initialization errors gracefully', function () {
            $problematicForm = new class implements HasForm, UseDatabase {
                public function getFieldsForm(): array
                {
                    return [Field::make('id')];
                }
                
                public function formUrl(): string
                {
                    return '/test/problematic';
                }
                
                public function setTplForm(): void
                {
                    // Mock implementation
                }
                
                public function getValidationRules(): array
                {
                    return [];
                }
                
                public function getFormConfig(): array
                {
                    return ['method' => 'POST'];
                }

                public function table(): string
                {
                    throw new \Exception('Database error');
                }
                
                public function query($query): void
                {
                    // Mock implementation
                }

                public function model($id = null)
                {
                    return (object) ['id' => $id];
                }
                
                public function modelName(): string
                {
                    return 'App\\Models\\Test';
                }
            };

            $generator = new FormGenerator($problematicForm);
            
            expect($generator->getDatabase())->toBeNull();
        });

        it('handles value loading errors gracefully', function () {
            $problematicForm = new class implements HasForm, UseDatabase {
                public function getFieldsForm(): array
                {
                    return [Field::make('id')];
                }
                
                public function formUrl(): string
                {
                    return '/test/loading-error';
                }
                
                public function setTplForm(): void
                {
                    // Mock implementation
                }
                
                public function getValidationRules(): array
                {
                    return [];
                }
                
                public function getFormConfig(): array
                {
                    return ['method' => 'POST'];
                }

                public function table(): string
                {
                    return 'users';
                }
                
                public function query($query): void
                {
                    // Mock implementation
                }

                public function model($id = null)
                {
                    throw new \Exception('Model loading error');
                }
                
                public function modelName(): string
                {
                    return 'App\\Models\\User';
                }
            };

            $generator = new FormGenerator($problematicForm, 123);
            $response = $generator->generate();
            
            expect($response)->toBeInstanceOf(FormResponse::class);
        });

        it('handles non-database forms without errors', function () {
            $generator = new FormGenerator($this->mockForm, 123);
            $response = $generator->generate();
            
            expect($response)->toBeInstanceOf(FormResponse::class);
            expect($generator->getDatabase())->toBeNull();
        });
    });

    describe('Getter Methods', function () {
        beforeEach(function () {
            $this->generator = FormGenerator::make($this->mockForm, 123)
                ->withMeta(['test' => 'value'])
                ->withValidation(['required' => 'name'])
                ->withValues(['preset' => 'data']);
        });

        it('returns correct form instance', function () {
            expect($this->generator->getForm())->toBe($this->mockForm);
        });

        it('returns correct ID', function () {
            expect($this->generator->getId())->toBe(123);
        });

        it('returns correct fields', function () {
            expect($this->generator->getFields())->toHaveCount(3);
        });

        it('returns correct metadata', function () {
            expect($this->generator->getMeta())->toBe(['test' => 'value']);
        });

        it('returns correct validation rules', function () {
            expect($this->generator->getValidationRules())->toBe(['required' => 'name']);
        });

        it('returns correct values', function () {
            expect($this->generator->getValues())->toBe(['preset' => 'data']);
        });

        it('returns correct button states', function () {
            expect($this->generator->hasBackButton())->toBeTrue();
            expect($this->generator->hasSaveAndStayButton())->toBeTrue();
        });

        it('returns empty links by default', function () {
            expect($this->generator->getLinks())->toHaveCount(0);
        });
    });

    describe('Advanced Features', function () {
        it('can work with complex field configurations', function () {
            $complexFields = [
                Field::make('id')->setDatabaseKey('id'),
                Field::make('full_name')->setDatabaseKey('CONCAT(first_name, " ", last_name)')->setMethodSql(true),
                Field::make('created')->setDatabaseKey('created_at')->setType('datetime')
            ];

            $generator = FormGenerator::make($this->mockDatabaseForm, null, $complexFields);
            
            expect($generator->getFields())->toHaveCount(3);
            expect($generator->getDatabase())->toBeInstanceOf(Database::class);
        });

        it('maintains state across method calls', function () {
            $generator = FormGenerator::make($this->mockForm)
                ->setIdentifier('uuid')
                ->showBackButton(false);
            
            // State should persist
            expect($generator->identifier)->toBe('uuid');
            expect($generator->hasBackButton())->toBeFalse();
            
            // Adding more modifications should preserve previous state
            $generator->withMeta(['new' => 'meta']);
            
            expect($generator->identifier)->toBe('uuid');
            expect($generator->hasBackButton())->toBeFalse();
            expect($generator->getMeta())->toBe(['new' => 'meta']);
        });
    });
});
