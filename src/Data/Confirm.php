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
     * The type/icon of the confirmation dialog.
     *
     * @var string
     */
    public string $type;

    /**
     * The action name.
     *
     * @var string
     */
    public string $action;

    /**
     * Confirm button text.
     *
     * @var string
     */
    public string $confirmButtonText = 'Confirm';

    /**
     * Cancel button text.
     *
     * @var string
     */
    public string $cancelButtonText = 'Cancel';

    /**
     * Confirm constructor.
     *
     * @param string $title
     * @param string $message
     * @param string $type
     * @param string $action
     */
    public function __construct(string $title, string $message, string $type = 'warning', string $action = 'confirm')
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->action = $action;
    }

    /**
     * Set confirm button text.
     *
     * @param string $text
     * @return $this
     */
    public function confirmButton(string $text): self
    {
        $this->confirmButtonText = $text;
        return $this;
    }

    /**
     * Set cancel button text.
     *
     * @param string $text
     * @return $this
     */
    public function cancelButton(string $text): self
    {
        $this->cancelButtonText = $text;
        return $this;
    }
}
