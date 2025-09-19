<?php

namespace RMS\Core\Data;

use RMS\Core\Data\Confirm;

/**
 * Class for defining a batch action.
 */
class BatchAction
{
    /**
     * The title of the batch action.
     *
     * @var string
     */
    public string $title;

    /**
     * The CSS class for the batch action.
     *
     * @var string
     */
    public string $class;

    /**
     * The URL for the batch action.
     *
     * @var string
     */
    public string $url;

    /**
     * The confirmation object for the batch action.
     *
     * @var Confirm|bool
     */
    public Confirm|bool $confirm = false;

    /**
     * Additional HTML attributes for the batch action.
     *
     * @var array
     */
    public array $attributes = [];

    /**
     * BatchAction constructor.
     *
     * @param string $title
     * @param string $url
     * @param string $class
     */
    public function __construct(string $title, string $url, string $class)
    {
        $this->title = $title;
        $this->url = $url;
        $this->class = $class;
    }

    /**
     * Set the confirmation for the batch action.
     *
     * @param Confirm $confirm
     * @return $this
     */
    public function confirm(Confirm $confirm): self
    {
        $this->confirm = $confirm;
        return $this;
    }

    /**
     * Add additional HTML attributes to the batch action.
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
