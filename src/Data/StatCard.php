<?php

declare(strict_types=1);

namespace RMS\Core\Data;

/**
 * Statistical Card Data Object
 * 
 * Represents a single statistical card for use in both List and Form statistics.
 * 
 * @package RMS\Core\Data
 */
class StatCard
{
    /**
     * Card title (e.g., "مجموع کاربران")
     */
    public string $title;

    /**
     * Main value to display (e.g., "1,234")
     */
    public string $value;

    /**
     * Unit of measurement (e.g., "نفر", "تومان") 
     */
    public string $unit;

    /**
     * PhosphorIcons icon name (e.g., "users", "currency-dollar")
     */
    public string $icon;

    /**
     * Bootstrap color scheme (primary, success, danger, warning, info, secondary)
     */
    public string $color;

    /**
     * Bootstrap column classes (e.g., "col-xl-3 col-md-6")
     */
    public string $colSize;

    /**
     * Optional description or subtitle
     */
    public ?string $description;

    /**
     * Whether to show border around the card
     */
    public bool $showBorder;

    /**
     * Create a new StatCard instance.
     *
     * @param string $title Card title
     * @param string $value Main value to display  
     * @param string $unit Unit of measurement
     * @param string $icon PhosphorIcons icon name
     * @param string $color Bootstrap color scheme
     * @param string $colSize Bootstrap column classes
     * @param string|null $description Optional description
     * @param bool $showBorder Whether to show border
     */
    public function __construct(
        string $title,
        string $value,
        string $unit = '',
        string $icon = 'chart-bar',
        string $color = 'primary',
        string $colSize = 'col-xl-3 col-md-6',
        ?string $description = null,
        bool $showBorder = true
    ) {
        $this->title = $title;
        $this->value = $value;
        $this->unit = $unit;
        $this->icon = $icon;
        $this->color = $color;
        $this->colSize = $colSize;
        $this->description = $description;
        $this->showBorder = $showBorder;
    }

    /**
     * Create a StatCard using fluent interface.
     *
     * @param string $title
     * @param string $value
     * @return static
     */
    public static function make(string $title, string $value): static
    {
        return new static($title, $value);
    }

    /**
     * Set the unit of measurement.
     *
     * @param string $unit
     * @return $this
     */
    public function withUnit(string $unit): self
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * Set the icon.
     *
     * @param string $icon PhosphorIcons icon name
     * @return $this
     */
    public function withIcon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Set the color scheme.
     *
     * @param string $color Bootstrap color (primary, success, danger, warning, info, secondary)
     * @return $this
     */
    public function withColor(string $color): self
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Set the column size.
     *
     * @param string $colSize Bootstrap column classes
     * @return $this
     */
    public function withColSize(string $colSize): self
    {
        $this->colSize = $colSize;
        return $this;
    }

    /**
     * Set the description.
     *
     * @param string $description
     * @return $this
     */
    public function withDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set border visibility.
     *
     * @param bool $showBorder
     * @return $this
     */
    public function withBorder(bool $showBorder = true): self
    {
        $this->showBorder = $showBorder;
        return $this;
    }

    /**
     * Convert to array for compatibility with existing components.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'value' => $this->value,
            'unit' => $this->unit,
            'icon' => $this->icon,
            'color' => $this->color,
            'colSize' => $this->colSize,
            'description' => $this->description,
            'showBorder' => $this->showBorder,
        ];
    }

    /**
     * Quick factory methods for common card types.
     */

    /**
     * Create a user count card.
     *
     * @param int $count
     * @param string $description
     * @return static
     */
    public static function userCount(int $count, string $description = 'کاربران'): static
    {
        return static::make($description, number_format($count))
            ->withUnit('نفر')
            ->withIcon('users')
            ->withColor('primary');
    }

    /**
     * Create a money/price card.
     *
     * @param float $amount
     * @param string $description
     * @param string $currency
     * @return static
     */
    public static function money(float $amount, string $description = 'مبلغ', string $currency = 'تومان'): static
    {
        return static::make($description, number_format($amount))
            ->withUnit($currency)
            ->withIcon('currency-dollar')
            ->withColor('success');
    }

    /**
     * Create a percentage card.
     *
     * @param float $percentage
     * @param string $description
     * @return static
     */
    public static function percentage(float $percentage, string $description = 'درصد'): static
    {
        return static::make($description, number_format($percentage, 1))
            ->withUnit('%')
            ->withIcon('percent')
            ->withColor('info');
    }

    /**
     * Create a status card.
     *
     * @param string $status
     * @param string $description
     * @param string $color
     * @return static
     */
    public static function status(string $status, string $description = 'وضعیت', string $color = 'primary'): static
    {
        return static::make($description, $status)
            ->withIcon('check-circle')
            ->withColor($color);
    }
}