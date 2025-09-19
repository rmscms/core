<?php

namespace RMS\Core\Data;

use Illuminate\Support\Collection;

/**
 * Trait for managing select fields.
 */
trait Select
{
    /**
     * The ID column for select options.
     *
     * @var string|null
     */
    public ?string $select_id;

    /**
     * The title column for select options.
     *
     * @var string|null
     */
    public ?string $select_title;

    /**
     * The data for select options.
     *
     * @var Collection|null
     */
    public ?Collection $select_data;

    /**
     * Whether to use Select2 for advanced select.
     *
     * @var bool
     */
    public bool $advanced = false;

    /**
     * Whether the select allows multiple selections.
     *
     * @var bool
     */
    public bool $multiple = false;

    /**
     * Whether to include an empty option.
     *
     * @var bool
     */
    public bool $empty_option = false;

    /**
     * Set the ID column for select options.
     *
     * @param string $select_id
     * @return $this
     */
    public function setSelectId(string $select_id): self
    {
        $this->select_id = $select_id;
        return $this;
    }

    /**
     * Set the title column for select options.
     *
     * @param string $select_title
     * @return $this
     */
    public function setSelectTitle(string $select_title): self
    {
        $this->select_title = $select_title;
        return $this;
    }

    /**
     * Set the data for select options.
     *
     * @param array|Collection $data
     * @param string $select_id
     * @param string $select_title
     * @return $this
     */
    public function setSelectData($data, string $select_id = 'id', string $select_title = 'name'): self
    {
        $this->select_data = $data instanceof Collection ? $data : collect($data);
        return $this->setSelectId($select_id)->setSelectTitle($select_title);
    }

    /**
     * Enable advanced select (e.g., Select2).
     *
     * @return $this
     */
    public function advanced(): self
    {
        $this->advanced = true;
        return $this;
    }

    /**
     * Enable multiple select.
     *
     * @return $this
     */
    public function multipleSelect(): self
    {
        $this->multiple = true;
        return $this;
    }

    /**
     * Include an empty option in the select.
     *
     * @return $this
     */
    public function withEmptyOption(): self
    {
        $this->empty_option = true;
        return $this;
    }

    /**
     * Set options for select field (for filter purposes).
     * 
     * Can accept both formats:
     * - Array with key-value pairs: ['1' => 'Active', '0' => 'Inactive']
     * - Collection with objects: [['id' => '1', 'name' => 'Active'], ...]
     *
     * @param array|Collection $options
     * @param string $idKey Default key for option value (default: 'id')
     * @param string $nameKey Default key for option text (default: 'name')
     * @return $this
     */
    public function setOptions(array|\Illuminate\Support\Collection $options, string $idKey = 'id', string $nameKey = 'name'): self
    {
        if (is_array($options)) {
            // Convert key-value array to collection of objects for blade compatibility
            $collection = collect();
            foreach ($options as $key => $value) {
                $collection->push([
                    $idKey => $key,
                    $nameKey => $value
                ]);
            }
            $options = $collection;
        }
        
        return $this->setSelectData($options, $idKey, $nameKey);
    }
}
