<?php

namespace RMS\Core\View\HelperList;

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
    public $title;

    /**
     * The URL of the link.
     *
     * @var string
     */
    public $url;

    /**
     * The CSS class for the link.
     *
     * @var string|null
     */
    public $class;

    /**
     * The icon for the link (e.g., Phosphor icon class).
     *
     * @var string|null
     */
    public $icon;

    /**
     * Additional HTML attributes for the link.
     *
     * @var array
     */
    public $attributes = [];

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
    public function withAttributes(array $attributes): static
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }
}
