<?php

namespace RMS\Core\Data;

use RMS\Core\Contracts\Data\UseDatabase;
use RMS\Core\Contracts\Form\HasForm;
use InvalidArgumentException;

/**
 * Enhanced form generator with improved Database integration.
 * 
 * This class generates forms with full Database class support,
 * providing fluent interfaces and enhanced query capabilities.
 */
class FormGenerator
{
    /**
     * Identifier key for model instance.
     *
     * @var string
     */
    public string $identifier = 'id';

    /**
     * Form instance implementing HasForm.
     *
     * @var HasForm
     */
    public HasForm $form;

    /**
     * Array of form fields.
     *
     * @var array
     */
    public array $fields = [];

    /**
     * Model ID for loading form values.
     *
     * @var int|string|null
     */
    public int|string|null $id = null;

    /**
     * Values of the form.
     *
     * @var array
     */
    public array $values = [];

    /**
     * Whether to display the back button.
     *
     * @var bool
     */
    public bool $back_button = true;

    /**
     * Array of links for the form.
     *
     * @var array
     */
    public array $links = [];

    /**
     * Whether to display the save and stay button.
     *
     * @var bool
     */
    public bool $save_and_stay_button = true;

    /**
     * Additional metadata for the form.
     *
     * @var array
     */
    public array $meta = [];

    /**
     * Validation rules for the form.
     *
     * @var array
     */
    public array $validation_rules = [];

    /**
     * Database instance for data operations.
     *
     * @var Database|null
     */
    protected ?Database $database = null;

    /**
     * FormGenerator constructor.
     *
     * @param HasForm $form
     * @param int|string|null $id
     * @param array $fields
     * @throws InvalidArgumentException
     */
    public function __construct(HasForm $form, int|string|null $id = null, array $fields = [])
    {
        $this->form = $form;
        $this->fields = $fields ?: $form->getFieldsForm();
        $this->id = $id;
        
        $this->validateFields();
        $this->initializeDatabase();
    }

    /**
     * Static factory method for creating FormGenerator instances.
     *
     * @param HasForm $form
     * @param int|string|null $id
     * @param array $fields
     * @return static
     */
    public static function make(HasForm $form, int|string|null $id = null, array $fields = []): static
    {
        return new static($form, $id, $fields);
    }

    /**
     * Generate the form response with enhanced data loading.
     *
     * @return FormResponse|null
     */
    public function generate(): ?FormResponse
    {
        $values = $this->loadFormValues();
        return new FormResponse($this, $this->form, $values);
    }

    /**
     * Load form values using Database class or fallback method.
     * Filters out fields marked with skip_database.
     *
     * @return array
     */
    protected function loadFormValues(): array
    {
        if (!$this->form instanceof UseDatabase || !$this->id) {
            return [];
        }

        try {
            $rawValues = [];
            
            if ($this->database) {
                // Use enhanced Database class
                $record = $this->database
                    ->where($this->identifier, '=', $this->id)
                    ->first();
                
                $rawValues = $record ? (array) $record : [];
            } else {
                // Fallback to model method
                $model = $this->form->model($this->id);
                if (!$model) {
                    return [];
                }
                
                // Handle both Eloquent models and plain objects
                if (method_exists($model, 'toArray')) {
                    $rawValues = $model->toArray();
                } else {
                    $rawValues = (array) $model;
                }
            }
            
            // ✅ فیلتر کردن فیلدهای skip_database
            return $this->filterSkipDatabaseFields($rawValues);
            
        } catch (\Exception $e) {
            // Log error and return empty array for graceful degradation
            error_log('FormGenerator: Error loading values - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Initialize Database instance if form uses database.
     *
     * @return void
     */
    protected function initializeDatabase(): void
    {
        if ($this->form instanceof UseDatabase) {
            try {
                $tableName = $this->form->table();
                
                // Filter out fields that don't have database columns or skip_database = true
                $databaseFields = array_filter($this->fields, function($field) {
                    return $field instanceof Field && 
                           $field->database_key !== null && 
                           (!isset($field->skip_database) || $field->skip_database !== true); // ✅ حذف skipDatabase fields
                });
                
                $this->database = Database::make($databaseFields, $tableName);
                
                // Apply any security constraints from the form
                $this->applyFormSecurityConstraints();
                
            } catch (\Exception $e) {
                // Log warning but continue without database
                error_log('FormGenerator: Could not initialize Database - ' . $e->getMessage());
                $this->database = null;
            }
        }
    }

    /**
     * Apply security constraints from form to database.
     *
     * @return void
     */
    protected function applyFormSecurityConstraints(): void
    {
        if (!$this->database || !method_exists($this->form, 'getSecurityConstraints')) {
            return;
        }

        $constraints = $this->form->getSecurityConstraints();
        
        if (is_array($constraints)) {
            foreach ($constraints as $constraint) {
                if (isset($constraint['column'], $constraint['operator'], $constraint['value'])) {
                    $this->database->addSecurityConstraint(
                        $constraint['column'],
                        $constraint['operator'],
                        $constraint['value']
                    );
                }
            }
        }
    }

    /**
     * Validate that all fields are Field instances.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateFields(): void
    {
        foreach ($this->fields as $field) {
            if (!$field instanceof Field) {
                throw new InvalidArgumentException('All fields must be instances of Field class');
            }
        }
    }

    /**
     * Add a link to the form.
     *
     * @param Link $link
     * @return $this
     */
    public function link(Link $link): self
    {
        $this->links[] = $link;
        return $this;
    }

    /**
     * Add metadata to the form.
     *
     * @param array $meta
     * @return $this
     */
    public function withMeta(array $meta): self
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    /**
     * Set validation rules for the form.
     *
     * @param array $rules
     * @return $this
     */
    public function withValidation(array $rules): self
    {
        $this->validation_rules = $rules;
        return $this;
    }

    /**
     * Set form identifier key.
     *
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Enable/disable back button.
     *
     * @param bool $show
     * @return $this
     */
    public function showBackButton(bool $show = true): self
    {
        $this->back_button = $show;
        return $this;
    }

    /**
     * Enable/disable save and stay button.
     *
     * @param bool $show
     * @return $this
     */
    public function showSaveAndStayButton(bool $show = true): self
    {
        $this->save_and_stay_button = $show;
        return $this;
    }

    /**
     * Add custom values to the form.
     *
     * @param array $values
     * @return $this
     */
    public function withValues(array $values): self
    {
        $this->values = array_merge($this->values, $values);
        return $this;
    }

    /**
     * Get the Database instance if available.
     *
     * @return Database|null
     */
    public function getDatabase(): ?Database
    {
        return $this->database;
    }

    /**
     * Get the form instance.
     *
     * @return HasForm
     */
    public function getForm(): HasForm
    {
        return $this->form;
    }

    /**
     * Get the form fields.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Get the form values.
     *
     * @return array
     */
    public function getValues(): array
    {
        // If values are not loaded yet, load them
        if (empty($this->values) && $this->id) {
            $this->values = $this->loadFormValues();
        }
        
        return $this->values;
    }

    /**
     * Get the form ID.
     *
     * @return int|string|null
     */
    public function getId(): int|string|null
    {
        return $this->id;
    }

    /**
     * Get form metadata.
     *
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * Get validation rules.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return $this->validation_rules;
    }

    /**
     * Check if form has back button enabled.
     *
     * @return bool
     */
    public function hasBackButton(): bool
    {
        return $this->back_button;
    }

    /**
     * Check if form has save and stay button enabled.
     *
     * @return bool
     */
    public function hasSaveAndStayButton(): bool
    {
        return $this->save_and_stay_button;
    }

    /**
     * Get form links.
     *
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * Filter out values for fields marked with skip_database.
     * This ensures virtual fields don't interfere with database operations.
     *
     * @param array $values
     * @return array
     */
    protected function filterSkipDatabaseFields(array $values): array
    {
        $filtered = [];
        
        foreach ($values as $fieldKey => $value) {
            $shouldInclude = true;
            
            // جستجو فیلد در form fields
            foreach ($this->fields as $field) {
                if ($field instanceof Field && $field->key === $fieldKey) {
                    // اگر فیلد skip_database دارد, حذف کن
                    if (isset($field->skip_database) && $field->skip_database === true) {
                        $shouldInclude = false;
                    }
                    break;
                }
            }
            
            if ($shouldInclude) {
                $filtered[$fieldKey] = $value;
            }
        }
        
        return $filtered;
    }
}
