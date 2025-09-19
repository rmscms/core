<?php

namespace RMS\Core\Data;

/**
 * Class representing a link in a list.
 */
class Link
{
    /**
     * The title of the link.
     *
     * @var string
     */
    public string $title;

    /**
     * The URL of the link.
     *
     * @var string
     */
    public string $url;

    /**
     * The CSS class for the link.
     *
     * @var string|null
     */
    public ?string $class;

    /**
     * The icon for the link (e.g., Phosphor icon class).
     *
     * @var string|null
     */
    public ?string $icon;

    /**
     * Additional HTML attributes for the link.
     *
     * @var array
     */
    public array $attributes = [];

    /**
     * Link constructor.
     *
     * @param string $title
     * @param string $url
     * @param string|null $class
     * @param string|null $icon
     */
    public function __construct(string $title, string $url, ?string $class = null, ?string $icon = null)
    {
        $this->title = $title;
        $this->url = $url;
        $this->class = $class;
        $this->icon = $icon;
    }

    /**
     * Add additional HTML attributes to the link.
     *
     * @param array $attributes
     * @return $this
     */
    public function withAttributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }
}
