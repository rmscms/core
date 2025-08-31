<?php

namespace RMS\Core\Data;

use RMS\Core\Data\FieldLink;
use RMS\Core\Data\Select;
use RMS\Core\Data\Filter;
use RMS\Core\Data\Style;
use RMS\Core\Data\BoolOptions;
use RMS\Core\Data\Input;
use RMS\Core\Data\Export;

/**
 * Field list options generator
 */
class Field
{
    use Select, Filter, Style, BoolOptions, Input, Export, FieldLink;

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
     * Field constructor.
     *
     * @param string $key
     * @param string|null $database_column
     * @param bool $method_sql
     */
    public function __construct(string $key, ?string $database_column = null, bool $method_sql = false)
    {
        $this->key = $key;
        $this->database_key = $database_column;
        $this->method_sql = $method_sql;
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
            self::NUMBER, self::RANGE
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
            self::SELECT => 'select',
            self::HIDDEN => 'hidden',
        ];
        return $types[$this->type] ?? 'text';
    }
}
