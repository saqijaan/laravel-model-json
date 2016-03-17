<?php

namespace ModelJsonColumn;

class JsonColumnValue
{
    /**
     * Holds the original data provided from the model.
     *
     * @var string
     */
    private $original_value = '';

    /**
     * Holds the current data.
     *
     * @var array
     */
    private $data = [];

    /**
     * Holds the original data provided by the model.
     *
     * @var array
     */
    private $original_data = [];

    /**
     * Add the data to the object.
     *
     * @return array
     */
    public function __construct(& $value)
    {
        $this->original_value = & $value;
        if (!is_array($value)) {
            $this->data = json_decode($this->original_value, true);
        } else {
            $this->data = $this->original_value;
        }
        $this->original_data = $this->data;
        foreach ($this->data as $key => &$value) {
            $this->$key = &$value;
        }
    }

    /**
     * Get the json data original values.
     *
     * @param string|null $key
     * @param mixed       $default
     *
     * @return array
     */
    public function getOriginal($key = null, $default = null)
    {
        if ($key === null) {
            return $this->original_data;
        } elseif (array_key_exists($key, $this->original_data)) {
            return $this->original_data[$key];
        } else {
            return $default;
        }
    }

    /**
     * Get the current data as an array.
     *
     * @return array
     */
    public function getCurrent()
    {
        return $this->data;
    }

    /**
     * Get the current data json encoded.
     *
     * @return string
     */
    public function getJson()
    {
        $dirty = $this->getDirty();
        if (count($dirty)) {
            $this->original_value = $this->__toString();
        }

        return $this->original_value;
    }

    /**
     * Add json data values that have changed.
     *
     * @return array
     */
    public function getDirty()
    {
        $dirty = [];
        foreach ($this->data as $key => $value) {
            if (array_key_exists($key, $this->original_data)
                && $this->original_data[$key] != $value) {
                $dirty[$key] = $value;
            } elseif (!array_key_exists($key, $this->original_data)) {
                $dirty[$key] = $value;
            }
        }
        $this->original_value = (string)$this;
        return $dirty;
    }

    /**
     * Track new column additions.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        if (!array_key_exists($key, $this->data)) {
            $this->data[$key] = $value;
            $this->{$key} = &$this->data[$key];
        }
    }

    /**
     * Convert to json encoded string.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->data);
    }
}
