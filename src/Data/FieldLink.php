<?php

namespace RMS\Core\Data;

/**
 * Trait for attaching a link to a field.
 */
trait FieldLink
{
    /**
     * The link associated with the field.
     *
     * @var Link|bool
     */
    public Link|bool $url = false;

    /**
     * Set the link for the field.
     *
     * @param Link $url
     * @return $this
     */
    public function withUrl(Link $url): self
    {
        $this->url = $url;
        return $this;
    }
}
