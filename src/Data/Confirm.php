<?php

namespace RMS\Core\Data;

/**
 * Class for defining confirmation dialogs for actions.
 */
class Confirm
{
    /**
     * The title of the confirmation dialog.
     *
     * @var string
     */
    public string $title;

    /**
     * The message of the confirmation dialog.
     *
     * @var string
     */
    public string $message;

    /**
     * Confirm constructor.
     *
     * @param string $title
     * @param string $message
     */
    public function __construct(string $title, string $message)
    {
        $this->title = $title;
        $this->message = $message;
    }
}
