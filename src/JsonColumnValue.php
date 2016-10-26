<?php

namespace Bluora\LaravelModelJson;

class JsonColumnValue
{
    /**
     * Holds the original data provided from the model.
     *
     * @var string
     */
    private $internal_original_value = '';

    /**
     * Holds the original data provided by the model.
     *
     * @var array
     */
    private $internal_original_data = [];

    /**
     * Holds specific defaults.
     *
     * @var array
     */
    private $internal_defaults = [];

    /**
     * Holds specific options to internally deal with values.
     *
     * @var array
     */
    private $internal_options = [];

    /**
     * Add the data to the object.
     *
     * @return array
     */
    public function __construct(&$attribute_value, $defaults, $options)
    {
        $this->internal_original_value = &$attribute_value;
        $this->internal_original_data = (!is_array($attribute_value)) ? json_decode($this->internal_original_value, true) : $this->internal_original_value;
        $this->internal_defaults = $defaults;
        $this->internal_options = $options;
        foreach ($this->internal_original_data as $key => $value) {
            $this->$key = $value;
            unset($value);
        }
        foreach ($defaults as $key => $value) {
            if (!isset($this->$key)) {
                $this->$key = $value;
            }
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
            return $this->internal_original_data;
        } elseif (array_key_exists($key, $this->internal_original_data)) {
            return $this->internal_original_data[$key];
        }

        return $default;
    }

    /**
     * Get the current data as an array.
     *
     * @return array
     */
    public function getCurrent()
    {
        $data = [];
        foreach (get_object_vars($this) as $key => $value) {
            if (substr($key, 0, 8) !== 'internal') {
                $data[$key] = $value;
            }
        }

        return $data;
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
            $this->internal_original_value = $this->__toString();
        }

        return $this->internal_original_value;
    }

    /**
     * Add json data values that have changed.
     *
     * @return array
     */
    public function getDirty()
    {
        $dirty = [];
        foreach ($this->getCurrent() as $key => $value) {

            // Value is a default value and `no_saving_default_values` option has been enabled
            if (array_key_exists('no_saving_default_values', $this->internal_options)
                && $this->internal_options['no_saving_default_values']
                && array_key_exists($key, $this->internal_defaults)
                && $this->internal_defaults[$key] === $value) {
                // Do nothing
            }

            // Existing value has changed
            elseif (array_key_exists($key, $this->internal_original_data)
                && $this->internal_original_data[$key] !== $value) {
                $dirty[$key] = $value;
            }

            // New value that has been assigned
            elseif (!array_key_exists($key, $this->internal_original_data)) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Set value.
     *
     * @param array $data
     */
    public function setValue($key, $value)
    {
        $this->$key = $value;
    }

    /**
     * Set values.
     *
     * @param array $data
     */
    public function setValues($data)
    {
        if (!is_array($data)) {
            return $this;
        }

        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Track new column additions.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->$key = $value;
    }

    /**
     * Get a value.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function &__get($key)
    {
        if (!isset($this->$key)) {
            $this->$key = null;
        }
        return $this->$key;
    }

    /**
     * Check if key exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->$key);
    }

    /**
     * Remove a key.
     *
     * @param string $key
     */
    public function __unset($key)
    {
        unset($this->$key);
    }

    /**
     * Convert to json encoded string.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->getCurrent());
    }
}
