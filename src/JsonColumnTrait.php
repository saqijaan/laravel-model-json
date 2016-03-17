<?php

namespace ModelJsonColumn;

use Closure;

trait JsonColumnTrait
{
    /**
     * Holds the referenced json data.
     *
     * @var array
     */
    private $json_values = [];

    /**
     * Holds methods for accessing the json data.
     *
     * @var array
     */
    private $json_methods = [];

    /**
     * Create a new model instance that is existing.
     * Overrides parent to set Json columns.
     *
     * @param array       $attributes
     * @param string|null $connection
     *
     * @return static
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = parent::newFromBuilder($attributes, $connection);
        $model->inspectJson();

        return $model;
    }

    /**
     * Decodes each of the declared JSON attributes and allocates them to
     * the json value object. Binds closures based on the column name to
     * access and update the values.
     *
     * @return void
     */
    public function inspectJson()
    {
        if (!empty($this->json_columns)) {
            foreach ($this->json_columns as $column_name) {
                $this->processJson($column_name, $this->attributes[$column_name]);
            }
        }
    }

    public function processJson($column_name, & $value)
    {
        if (empty($value)) {
            $value = '';
        }
        $this->json_values[$column_name] = new JsonColumnValue($value);
        $json_column_access = function & ($column_name) {
            return $this->json_values[$column_name];
        };
        $this->json_methods[$column_name] = Closure::bind($json_column_access, $this, static::class);
    }

    /**
     * Set a given attribute on the known JSON elements.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function setAttribute($column_name, $value)
    {
        if (!empty($this->json_columns) && in_array($column_name, $this->json_columns)) {
            $this->attributes[$column_name] = $value;
            $this->processJson($column_name, $this->attributes[$column_name]);
            return;
        }
        parent::setAttribute($column_name, $value);
    }

    /**
     * Get the model's original attribute values.
     *
     * @param string|null $key
     * @param mixed       $default
     *
     * @return array
     */
    public function getOriginal($key = null, $default = null)
    {
        $original = parent::getOriginal($key, $default);
        if (!empty($this->json_values)) {
            if ($key === null) {
                foreach ($this->json_values as $column_name => &$json_data) {
                    $original[$column_name] = $json_data->getOriginal();
                }
            } else {
                $key_array = explode('.', $key, 2);
                if (count($key_array) > 1) {
                    list($column_name, $json_key) = $key_array;
                    if ($json_key != '' && array_key_exists($column_name, $this->json_values)) {
                        $original = $this->json_values[$column_name]->getOriginal($json_key, $default);
                    } else {
                        $original = $default;
                    }
                }
            }
        }
        return $original;
    }

    /**
     * Add the change in any json value objects, and if requested
     * provide the columns (dot notation) within the json value objects.
     *
     * @return array
     */
    public function getDirty($include_json = false)
    {
        $dirty = parent::getDirty();
        if (!empty($this->json_values)) {
            foreach ($this->json_values as $column_name => $json_data) {
                if (count($json_data->getDirty())) {
                    $dirty[$column_name] = (string) $json_data;
                }
            }
        }
        if (!$include_json) {
            return $dirty;
        }
        if (!empty($this->json_values)) {
            foreach ($this->json_values as $column_name => &$json_data) {
                $dirty_json = $json_data->getDirty();
                foreach ($dirty_json as $key => $value) {
                    $dirty[$column_name.'.'.$key] = $value;
                }
            }
        }
        return $dirty;
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        if (!empty($this->json_columns)) {
            foreach ($this->json_columns as $column_name) {
                $this->json_values[$column_name]->getDirty();
            }
        }
        return parent::attributesToArray();
    }

    /**
     * Handle dynamic method calls to the json value objects.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (isset($this->json_methods[$method])) {
            return call_user_func_array($this->json_methods[$method], [$method]);
        }
        return parent::__call($method, $parameters);
    }
}
