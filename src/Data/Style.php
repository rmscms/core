<?php

namespace RMS\Core\Data;

/**
 * Trait for managing field styles.
 */
trait Style
{
    /**
     * CSS class for each table cell.
     *
     * @var string|null
     */
    public ?string $class;

    /**
     * Width for each column.
     *
     * @var string
     */
    public string $width = 'auto';

    /**
     * Alignment for the column (center, left, right).
     *
     * @var string
     */
    public string $align = 'center';

    /**
     * Set the column width.
     *
     * @param string $width
     * @return $this
     */
    public function setWidth(string $width): self
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Set the column alignment.
     *
     * @param string $align
     * @return $this
     */
    public function setAlign(string $align): self
    {
        if (!in_array($align, ['center', 'left', 'right'])) {
            throw new \InvalidArgumentException('Invalid alignment value');
        }
        $this->align = $align;
        return $this;
    }

    /**
     * Set the CSS class for the field.
     *
     * @param string $class_name
     * @return $this
     */
    public function className(string $class_name): self
    {
        $this->class = $class_name;
        return $this;
    }
}
