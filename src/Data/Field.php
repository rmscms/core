<?php

namespace RMS\Core\Data;

use RMS\Core\Data\FieldLink;
use RMS\Core\Data\Select;
use RMS\Core\Data\Filter;
use RMS\Core\Data\Style;
use RMS\Core\Data\Input;
use RMS\Core\Data\Export;
use InvalidArgumentException;

/**
 * Field list options generator
 */
class Field
{
    use Select, Filter, Style, Input, Export, FieldLink;

    // Field types for list and form
    const STRING = 1;
    const DATE = 2;
    const INTEGER = 3;
    const BOOL = 4;
    const SELECT = 5;
    const PRICE = 6;
    const DATE_TIME = 7;
    const TIME = 8;
    const PASSWORD = 9;
    const HIDDEN = 10;
    const COMMENT = 11;
    const FILE = 12;
    const EDITOR = 13;
    const LABEL = 14;
    const COLOR = 15;
    const NUMBER = 16;
    const RANGE = 17;
    const IMAGE = 18;
    const RADIO = 19;
    const TEXTAREA = 20;

    /**
     * The field key.
     *
     * @var string
     */
    public string $key;

    /**
     * The database column key.
     *
     * @var string|null
     */
    public ?string $database_key;

    /**
     * The field title.
     *
     * @var string|null
     */
    public ?string $title;

    /**
     * The data type for display.
     *
     * @var int
     */
    public int $type = self::STRING;

    /**
     * Custom method for display instead of real data.
     *
     * @var string|bool
     */
    public string|bool $method = false;

    /**
     * Whether the database_key is an SQL method.
     *
     * @var bool
     */
    public bool $method_sql = false;

    /**
     * Additional HTML attributes for the field.
     *
     * @var array
     */
    public array $attributes = [];

    /**
     * Whether the field should be displayed in RTL.
     *
     * @var bool
     */
    public bool $rtl = false;

    /**
     * Title for the enabled state (for boolean fields).
     *
     * @var string|bool
     */
    public string|bool $enable_title = false;

    /**
     * Title for the disabled state (for boolean fields).
     *
     * @var string|bool
     */
    public string|bool $disable_title = false;

    /**
     * Value for the enabled state (for boolean fields).
     *
     * @var int
     */
    public int $enable = 1;

    /**
     * Value for the disabled state (for boolean fields).
     *
     * @var int
     */
    public int $disable = 0;

    /**
     * AJAX route for enable/disable actions in lists.
     *
     * @var string|bool
     */
    public string|bool $ajax_action_route = false;

    /**
     * Whether this field should be skipped when saving to database.
     * Useful for virtual fields, file uploads, or display-only fields.
     *
     * @var bool
     */
    public bool $skip_database = false;

    /**
     * Field constructor.
     *
     * @param string $key
     * @param string|null $database_column
     * @param bool $method_sql
     */
    public function __construct(string $key, ?string $database_column = null, bool $method_sql = false)
    {
        $this->key = $key;
        $this->database_key = $database_column ?? $key;
        $this->method_sql = $method_sql;
    }

    /**
     * Static factory method for creating Field instances.
     * This enables fluent method chaining from the start.
     *
     * @param string $key Field key name
     * @param string|null $database_column Database column name (defaults to key)
     * @param bool $method_sql Whether the database_key is an SQL method
     * @return static
     */
    public static function make(string $key, ?string $database_column = null, bool $method_sql = false): static
    {
        return new static($key, $database_column, $method_sql);
    }

    /**
     * Create a new Field instance and set its title in one call.
     *
     * @param string $key Field key name
     * @param string $title Field display title
     * @param string|null $database_column Database column name
     * @return static
     */
    public static function create(string $key, string $title, ?string $database_column = null): static
    {
        return static::make($key, $database_column)->withTitle($title);
    }

    /**
     * Set the field title.
     *
     * @param string $title
     * @return $this
     */
    public function withTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Set the database key for the field.
     *
     * @param string|null $databaseKey
     * @return $this
     */
    public function setDatabaseKey(?string $databaseKey): self
    {
        $this->database_key = $databaseKey;
        return $this;
    }

    /**
     * Set the field type.
     *
     * @param int $type
     * @return $this
     */
    public function setType(int $type): self
    {
        return $this->withType($type);
    }
    /**
     * Set the field type.
     *
     * @param int $type
     * @return $this
     */
    public function type(int $type): self
    {
        return $this->withType($type);
    }

    /**
     * Set method_sql flag.
     *
     * @param bool $methodSql
     * @return $this
     */
    public function setMethodSql(bool $methodSql = true): self
    {
        $this->method_sql = $methodSql;
        return $this;
    }

    /**
     * Set custom method.
     *
     * @param string|bool $method
     * @return $this
     */
    public function setMethod($method): self
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Set the field type.
     *
     * @param int $type
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function withType(int $type = self::STRING): self
    {
        if (!in_array($type, [
            self::STRING, self::DATE, self::INTEGER, self::BOOL, self::SELECT,
            self::PRICE, self::DATE_TIME, self::TIME, self::PASSWORD, self::HIDDEN,
            self::COMMENT, self::FILE, self::EDITOR, self::LABEL, self::COLOR,
            self::NUMBER, self::RANGE, self::IMAGE, self::RADIO, self::TEXTAREA
        ])) {
            throw new \InvalidArgumentException('Invalid field type');
        }
        $this->type = $type;
        return $this;
    }

    /**
     * Set a custom method for display.
     *
     * @param string|bool $method
     * @return $this
     */
    public function customMethod(string|bool $method): self
    {
        $this->method = $method;
        return $this;
    }
    /**
     * Set a custom method for display.
     *
     * @param string|bool $method
     * @return $this
     */
    public function method(string|bool $method): self
    {
        return $this->customMethod($method);
    }

    /**
     * Add additional HTML attributes to the field.
     *
     * @param array $attributes
     * @return $this
     */
    public function withAttributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Enable or disable RTL support for the field.
     *
     * @param bool $enable
     * @return $this
     */
    public function enableRTL(bool $enable = true): self
    {
        $this->rtl = $enable;
        return $this;
    }

    /**
     * Mark this field to be skipped when saving to database.
     * Useful for virtual fields, file uploads handled separately, or display-only fields.
     *
     * @param bool $skip
     * @return $this
     */
    public function skipDatabase(bool $skip = true): self
    {
        $this->skip_database = $skip;
        return $this;
    }

    /**
     * Mark this field as a virtual field (alias for skipDatabase).
     *
     * @return $this
     */
    public function virtual(): self
    {
        return $this->skipDatabase(true);
    }

    /**
     * Get the HTML input type for the field.
     *
     * @return string
     */
    public function inputType(): string
    {
        $types = [
            self::STRING => 'text',
            self::INTEGER => 'number',
            self::NUMBER => 'number',
            self::COLOR => 'color',
            self::RANGE => 'range',
            self::PRICE => 'text',
            self::PASSWORD => 'password',
            self::DATE => 'date',
            self::DATE_TIME => 'datetime-local',
            self::TIME => 'time',
            self::FILE => 'file',
            self::IMAGE => 'file',
            self::SELECT => 'select',
            self::HIDDEN => 'hidden',
            self::TEXTAREA => 'textarea',
            self::COMMENT => 'textarea',
            self::RADIO => 'radio',
        ];
        return $types[$this->type] ?? 'text';
    }

    // ========================================================================
    // Static Factory Methods for Common Field Types
    // ========================================================================

    /**
     * Create a string field.
     *
     * @param string $key
     * @param string|null $title
     * @param string|null $database_column
     * @return static
     */
    public static function string(string $key, ?string $title = null, ?string $database_column = null): static
    {
        $field = static::make($key, $database_column)->withType(self::STRING);
        return $title ? $field->withTitle($title) : $field;
    }

    /**
     * Create a password field.
     *
     * @param string $key
     * @param string|null $title
     * @return static
     */
    public static function password(string $key, ?string $title = null): static
    {
        $field = static::make($key)->withType(self::PASSWORD);
        return $title ? $field->withTitle($title) : $field;
    }

    /**
     * Create an email field.
     *
     * @param string $key
     * @param string|null $title
     * @return static
     */
    public static function email(string $key = 'email', ?string $title = null): static
    {
        return static::string($key, $title ?? 'ایمیل')
            ->withAttributes(['type' => 'email'])
            ->withValidation(['email']);
    }

    /**
     * Create a number field.
     *
     * @param string $key
     * @param string|null $title
     * @param int|null $min
     * @param int|null $max
     * @return static
     */
    public static function number(string $key, ?string $title = null, ?int $min = null, ?int $max = null): static
    {
        $field = static::make($key)->withType(self::NUMBER);
        if ($title) $field->withTitle($title);

        $attributes = [];
        if ($min !== null) $attributes['min'] = $min;
        if ($max !== null) $attributes['max'] = $max;

        return $attributes ? $field->withAttributes($attributes) : $field;
    }

    /**
     * Create a date field.
     *
     * @param string $key
     * @param string|null $title
     * @return static
     */
    public static function date(string $key, ?string $title = null): static
    {
        $field = static::make($key)->withType(self::DATE);
        return $title ? $field->withTitle($title) : $field;
    }

    /**
     * Create a datetime field.
     *
     * @param string $key
     * @param string|null $title
     * @return static
     */
    public static function datetime(string $key, ?string $title = null): static
    {
        $field = static::make($key)->withType(self::DATE_TIME);
        return $title ? $field->withTitle($title) : $field;
    }

    /**
     * Create a boolean field.
     *
     * @param string $key
     * @param string|null $title
     * @return static
     */
    public static function boolean(string $key, ?string $title = null): static
    {
        $field = static::make($key)->withType(self::BOOL);
        return $title ? $field->withTitle($title) : $field;
    }

    /**
     * Create a select field.
     *
     * @param string $key
     * @param string|null $title
     * @param array|\Illuminate\Support\Collection|null $options
     * @return static
     */
    public static function select(string $key, ?string $title = null, $options = null): static
    {
        $field = static::make($key)->withType(self::SELECT);
        if ($title) $field->withTitle($title);
        if ($options) $field->setSelectData($options);

        return $field;
    }

    /**
     * Create a file upload field.
     *
     * @param string $key
     * @param string|null $title
     * @param array $allowedTypes
     * @return static
     */
    public static function file(string $key, ?string $title = null, array $allowedTypes = []): static
    {
        $field = static::make($key)->withType(self::FILE);
        if ($title) $field->withTitle($title);

        if (!empty($allowedTypes)) {
            $field->withAttributes(['accept' => implode(',', $allowedTypes)]);
        }

        return $field;
    }

    /**
     * Create an image upload field with preview.
     * Note: Image fields are marked as skipDatabase() by default since they require special handling.
     *
     * @param string $key
     * @param string|null $title
     * @param array $options Configuration options
     * @return static
     */
    public static function image(string $key, ?string $title = null, array $options = []): static
    {
        $field = static::make($key)->withType(self::IMAGE)->skipDatabase(); // ✅ پیش‌فرض skipDatabase
        if ($title) $field->withTitle($title);

        // Default image options
        $defaultOptions = [
            'accept' => '.jpg,.jpeg,.png,.gif,.webp',
            'max_size' => '2MB',
            'preview' => true,
            'multiple' => false,
            'drag_drop' => true,
            'crop' => false,
            'resize' => ['width' => 800, 'height' => 600],
            'thumbnail' => ['width' => 150, 'height' => 150]
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // Set HTML attributes
        $field->withAttributes([
            'accept' => $options['accept'],
            'data-max-size' => $options['max_size'],
            'data-preview' => $options['preview'] ? 'true' : 'false',
            'data-drag-drop' => $options['drag_drop'] ? 'true' : 'false',
            'data-crop' => $options['crop'] ? 'true' : 'false',
            'data-resize' => json_encode($options['resize']),
            'data-thumbnail' => json_encode($options['thumbnail'])
        ]);
        
        if ($options['multiple']) {
            $field->withAttributes(['multiple' => true]);
        }
        
        // Add image validation rules
        $field->withValidation([
            'image',
            'mimes:jpeg,jpg,png,gif,webp',
            'max:' . (int)(str_replace('MB', '', $options['max_size']) * 1024)
        ]);

        return $field;
    }

    /**
     * Create a hidden field.
     *
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public static function hidden(string $key, $value = null): static
    {
        $field = static::make($key)->withType(self::HIDDEN);
        if ($value !== null) $field->withDefaultValue($value);

        return $field;
    }

    /**
     * Create a price/money field.
     *
     * @param string $key
     * @param string|null $title
     * @param string $currency
     * @return static
     */
    public static function price(string $key, ?string $title = null, string $currency = 'تومان'): static
    {
        $field = static::make($key)->withType(self::PRICE);
        if ($title) $field->withTitle($title);

        return $field->withAttributes(['data-currency' => $currency]);
    }

    /**
     * Create an editor field (rich text).
     *
     * @param string $key
     * @param string|null $title
     * @return static
     */
    public static function editor(string $key, ?string $title = null): static
    {
        $field = static::make($key)->withType(self::EDITOR);
        return $title ? $field->withTitle($title) : $field;
    }

    /**
     * Create a textarea field.
     *
     * @param string $key
     * @param string|null $title
     * @param int $rows
     * @return static
     */
    public static function textarea(string $key, ?string $title = null, int $rows = 4): static
    {
        $field = static::make($key)->withType(self::TEXTAREA);
        if ($title) $field->withTitle($title);
        
        $field->withAttributes(['rows' => $rows]);
        
        return $field;
    }

    /**
     * Create a color picker field.
     *
     * @param string $key
     * @param string|null $title
     * @return static
     */
    public static function color(string $key, ?string $title = null): static
    {
        $field = static::make($key)->withType(self::COLOR);
        return $title ? $field->withTitle($title) : $field;
    }

    // ========================================================================
    // Enhanced Fluent Interface Methods
    // ========================================================================

    /**
     * Set field as required and add validation.
     *
     * @param array $additionalRules Additional validation rules
     * @return $this
     */
    public function required(array $additionalRules = []): self
    {
        $this->required = true;
        $rules = array_merge(['required'], $additionalRules);
        return $this->withValidation($rules);
    }

    /**
     * Set field as optional.
     *
     * @param array $validationRules Optional validation rules
     * @return $this
     */
    public function optional(array $validationRules = []): self
    {
        $this->required = false;
        if (!empty($validationRules)) {
            $rules = array_merge(['nullable'], $validationRules);
            return $this->withValidation($rules);
        }
        return $this;
    }

    /**
     * Dynamically set the required status of the field.
     *
     * @param bool $required Whether the field is required
     * @return $this
     */
    public function setRequired(bool $required): self
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Add validation rules.
     *
     * @param array $rules
     * @return $this
     */
    public function withValidation(array $rules): self
    {
        $this->validation_rules = array_unique(array_merge($this->validation_rules, $rules));
        return $this;
    }

    /**
     * Set minimum and maximum length for string fields.
     *
     * @param int|null $min
     * @param int|null $max
     * @return $this
     */
    public function length(?int $min = null, ?int $max = null): self
    {
        $rules = [];
        if ($min !== null) $rules[] = "min:{$min}";
        if ($max !== null) $rules[] = "max:{$max}";

        return $this->withValidation($rules);
    }

    /**
     * Set field for Iranian mobile validation.
     *
     * @return $this
     */
    public function iranianMobile(): self
    {
        return $this->withValidation(['iranian_mobile'])
            ->withPlaceHolder('09xxxxxxxxx');
    }

    /**
     * Set field for Iranian national code validation.
     *
     * @return $this
     */
    public function iranianNationalCode(): self
    {
        return $this->withValidation(['iranian_national_code'])
            ->withPlaceHolder('کد ملی 10 رقمی');
    }

    /**
     * Set field for Persian text validation.
     *
     * @return $this
     */
    public function persianText(): self
    {
        return $this->withValidation(['persian_text']);
    }

    /**
     * Set field as sortable.
     *
     * @param bool $sortable
     * @return $this
     */
    public function sortable(bool $sortable = true): self
    {
        // Use the Filter trait's sort property, not attributes
        $this->sort = $sortable;
        return $this;
    }

    /**
     * Set field as searchable.
     *
     * @param bool $searchable
     * @return $this
     */
    public function searchable(bool $searchable = true): self
    {
        $this->attributes['searchable'] = $searchable;
        return $this;
    }

    /**
     * Set field width (for grid layouts).
     *
     * @param string $width
     * @return $this
     */
    public function width(string $width): self
    {
        $this->attributes['width'] = $width;
        return $this;
    }

    /**
     * Hide field from display but keep in forms.
     *
     * @return $this
     */
    public function hide(): self
    {
        $this->attributes['hidden'] = true;
        return $this;
    }

    /**
     * Set field as disabled.
     *
     * @return $this
     */
    public function disabled(): self
    {
        $this->attributes['disabled'] = true;
        return $this;
    }

    /**
     * Add CSS classes to the field.
     *
     * @param string|array $classes
     * @return $this
     */
    public function addClasses($classes): self
    {
        if (is_string($classes)) {
            $classes = explode(' ', $classes);
        }

        $existing = explode(' ', $this->class ?? '');
        $merged = array_unique(array_merge($existing, $classes));
        $this->class = implode(' ', array_filter($merged));

        return $this;
    }

    /**
     * Set field as filterable.
     *
     * @param bool $filterable
     * @return $this
     */
    public function filterable(bool $filterable = true): self
    {
        $this->attributes['filterable'] = $filterable;
        return $this;
    }

    /**
     * Check if field has a specific attribute.
     *
     * @param string $attribute
     * @return bool
     */
    public function hasAttribute(string $attribute): bool
    {
        return array_key_exists($attribute, $this->attributes);
    }

    /**
     * Get a specific attribute value.
     *
     * @param string $attribute
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute(string $attribute, $default = null)
    {
        return $this->attributes[$attribute] ?? $default;
    }

    /**
     * Convert field configuration to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'database_key' => $this->database_key,
            'title' => $this->title,
            'type' => $this->type,
            'method' => $this->method,
            'method_sql' => $this->method_sql,
            'attributes' => $this->attributes,
            'rtl' => $this->rtl,
            'validation_rules' => $this->validation_rules ?? [],
            'required' => $this->required ?? false,
            'place_holder' => $this->place_holder ?? false,
            'icon' => $this->icon ?? false,
            'class' => $this->class,
            'hint' => $this->hint,
        ];
    }

    /**
     * Create field from array configuration.
     *
     * @param array $config
     * @return static
     */
    public static function fromArray(array $config): static
    {
        $field = static::make(
            $config['key'],
            $config['database_key'] ?? null,
            $config['method_sql'] ?? false
        );

        if (isset($config['title'])) $field->withTitle($config['title']);
        if (isset($config['type'])) $field->withType($config['type']);
        if (isset($config['method'])) $field->customMethod($config['method']);
        if (isset($config['attributes'])) $field->withAttributes($config['attributes']);
        if (isset($config['rtl'])) $field->enableRTL($config['rtl']);

        return $field;
    }

    // ========================================================================
    // Boolean Field Methods (from BoolOptions trait)
    // ========================================================================

    /**
     * Set the title for the enabled state.
     *
     * @param string $title
     * @return $this
     */
    public function withEnableTitle(string $title): self
    {
        $this->enable_title = $title;
        return $this;
    }

    /**
     * Set the title for the disabled state.
     *
     * @param string $title
     * @return $this
     */
    public function withDisableTitle(string $title): self
    {
        $this->disable_title = $title;
        return $this;
    }

    /**
     * Set the values for enabled and disabled states.
     *
     * @param int $enable
     * @param int $disable
     * @return $this
     */
    public function withValues(int $enable = 1, int $disable = 0): self
    {
        $this->enable = $enable;
        $this->disable = $disable;
        return $this;
    }

    /**
     * Set the AJAX route for enable/disable actions.
     *
     * @param string $route
     * @return $this
     */
    public function ajaxActionRoute(string $route): self
    {
        $this->ajax_action_route = $route;
        return $this;
    }
}
