<?php

namespace RMS\Core\Data;

use RMS\Core\Data\Field;

/**
 * Class for defining a database column filter.
 */
class Column
{
    /**
     * The database column name.
     *
     * @var string
     */
    public string $column;

    /**
     * The filter operator (e.g., '=', 'LIKE').
     *
     * @var string
     */
    public string $operator;

    /**
     * The filter value.
     *
     * @var mixed
     */
    public $value;

    /**
     * The filter type (e.g., Field::STRING).
     *
     * @var int
     */
    public int $type;

    /**
     * Additional filter options.
     *
     * @var array
     */
    public array $options = [];

    /**
     * Whether the filter should be displayed in RTL.
     *
     * @var bool
     */
    public bool $rtl = false;

    /**
     * Validation rules for the filter value.
     *
     * @var array
     */
    public array $validation_rules = [];

    /**
     * Column constructor.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @param int $type
     */
    public function __construct(string $column, string $operator, $value, int $type)
    {
        $this->validateOperator($operator);
        $this->validateType($type);
        $this->column = $column;
        $this->operator = $operator;
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * Validate the filter operator.
     *
     * @param string $operator
     * @throws \InvalidArgumentException
     */
    protected function validateOperator(string $operator): void
    {
        $validOperators = ['=', '!=', '<', '>', '<=', '>=', 'LIKE', 'IN', 'NOT IN'];
        if (!in_array($operator, $validOperators)) {
            throw new \InvalidArgumentException("Invalid operator: $operator");
        }
    }

    /**
     * Validate the filter type.
     *
     * @param int $type
     * @throws \InvalidArgumentException
     */
    protected function validateType(int $type): void
    {
        if (!in_array($type, [
            Field::STRING, Field::DATE, Field::INTEGER, Field::BOOL, Field::SELECT,
            Field::PRICE, Field::DATE_TIME, Field::TIME, Field::PASSWORD, Field::HIDDEN,
            Field::COMMENT, Field::FILE, Field::EDITOR, Field::LABEL, Field::COLOR,
            Field::NUMBER, Field::RANGE
        ])) {
            throw new \InvalidArgumentException('Invalid filter type');
        }
    }

    /**
     * Set additional filter options.
     *
     * @param array $options
     * @return $this
     */
    public function withOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Enable or disable RTL support for the filter.
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
     * Set validation rules for the filter value.
     *
     * @param array $rules
     * @return $this
     */
    public function withValidation(array $rules): self
    {
        $this->validation_rules = $rules;
        return $this;
    }
}
