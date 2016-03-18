<?php

namespace ModelJsonColumn;

class JsonColumnValue
{
    /**
     * Holds the original data provided from the model.
     *
     * @var string
     */
    private static $original_value = '';

    /**
     * Holds the original data provided by the model.
     *
     * @var array
     */
    private static $original_data = [];

    /**
     * Holds specific defaults.
     *
     * @var array
     */
    private static $defaults = [];

    /**
     * Holds specific options to internally deal with values.
     *
     * @var array
     */
    private static $options = [];

    /**
     * Add the data to the object.
     *
     * @return array
     */
    public function __construct(&$attribute_value, $defaults, $options)
    {
        self::$original_value = &$attribute_value;
        self::$original_data = (!is_array($attribute_value)) ? json_decode(self::$original_value, true) : self::$original_value;
        self::$defaults = $defaults;
        self::$options = $options;
        foreach (self::$original_data as $key => $value) {
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
            return self::$original_data;
        } elseif (array_key_exists($key, self::$original_data)) {
            return self::$original_data[$key];
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
            $data[$key] = $value;
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
            self::$original_value = $this->__toString();
        }

        return self::$original_value;
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
            if (array_key_exists('no_saving_default_values', self::$options)
                && self::$options['no_saving_default_values']
                && array_key_exists($key, self::$defaults)
                && self::$defaults[$key] === $value) {
                // Do nothing
            }

            // Existing value has changed
            elseif (array_key_exists($key, self::$original_data)
                && self::$original_data[$key] !== $value) {
                $dirty[$key] = $value;
            }

            // New value that has been assigned
            elseif (!array_key_exists($key, self::$original_data)) {
                $dirty[$key] = $value;
            }
        }

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
        $this->$key = $value;
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
