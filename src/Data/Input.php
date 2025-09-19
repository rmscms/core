<?php

namespace RMS\Core\Data;

/**
 * Trait for managing input field properties.
 */
trait Input
{
    /**
     * Placeholder for input text.
     *
     * @var string|bool
     */
    public string|bool $place_holder = false;

    /**
     * Icon for the input field.
     *
     * @var string|bool
     */
    public string|bool $icon = false;

    /**
     * Whether to append or prepend the icon.
     *
     * @var bool
     */
    public bool $append_icon = false;

    /**
     * Additional HTML attributes for the input.
     *
     * @var array
     */
    public array $attributes = [];

    /**
     * Default value for the input.
     *
     * @var mixed
     */
    public $default_value = null;

    /**
     * CSS class for the input.
     *
     * @var string|null
     */
    public ?string $input_class = null;

    /**
     * Hint text for the input.
     *
     * @var string|null
     */
    public ?string $hint = null;

    /**
     * Whether the input is read-only.
     *
     * @var bool
     */
    public bool $read_only = false;

    /**
     * Size of the input (e.g., large).
     *
     * @var string|bool
     */
    public string|bool $size = false;

    /**
     * Whether the input is required.
     *
     * @var bool
     */
    public bool $required = false;

    /**
     * Whether the input is disabled.
     *
     * @var bool
     */
    public bool $disabled = false;

    /**
     * Validation rules for the input.
     *
     * @var array
     */
    public array $validation_rules = [];

    /**
     * Disable the input.
     *
     * @return $this
     */
    public function disable(): self
    {
        $this->disabled = true;
        return $this;
    }

    /**
     * Set the placeholder text.
     *
     * @param string $title
     * @return $this
     */
    public function withPlaceHolder(string $title): self
    {
        $this->place_holder = $title;
        return $this;
    }

    /**
     * Set the input icon.
     *
     * @param string $icon
     * @return $this
     */
    public function withIcon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Append or prepend the icon.
     *
     * @return $this
     */
    public function append(): self
    {
        $this->append_icon = true;
        return $this;
    }

    /**
     * Set additional HTML attributes.
     *
     * @param array $attributes
     * @return $this
     */
    public function withAttributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * Set the default value for the input.
     *
     * @param mixed $default
     * @return $this
     */
    public function withDefaultValue($default): self
    {
        $this->default_value = $default;
        return $this;
    }

    /**
     * Set the CSS class for the input.
     *
     * @param string $class
     * @return $this
     */
    public function withClass(string $class): self
    {
        $this->input_class = $class;
        return $this;
    }

    /**
     * Set the hint text for the input.
     *
     * @param string $hint
     * @return $this
     */
    public function withHint(string $hint): self
    {
        $this->hint = $hint;
        return $this;
    }

    /**
     * Set the input as read-only.
     *
     * @return $this
     */
    public function readOnly(): self
    {
        $this->read_only = true;
        return $this;
    }

    /**
     * Set the input size to large.
     *
     * @return $this
     */
    public function largeSize(): self
    {
        $this->size = 'large';
        return $this;
    }

    /**
     * Enable multiple select.
     *
     * @return $this
     */
    public function multiple(): self
    {
        $this->multiple = true;
        return $this;
    }

    /**
     * Set the input as required.
     *
     * @return $this
     */
    public function required(): self
    {
        $this->required = true;
        return $this;
    }

    /**
     * Set validation rules for the input.
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
     * Get placeholder text (alias for template compatibility).
     *
     * @return string|bool
     */
    public function getPlaceholderAttribute()
    {
        return $this->place_holder;
    }

    /**
     * Get readonly status (alias for template compatibility).
     *
     * @return bool
     */
    public function getReadonlyAttribute()
    {
        return $this->read_only;
    }

    /**
     * Magic getter for property access.
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        switch ($name) {
            case 'placeholder':
                return $this->place_holder;
            case 'readonly':
                return $this->read_only;
            default:
                return $this->{$name} ?? null;
        }
    }
}
