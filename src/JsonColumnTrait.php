<?php

namespace Bluora\LaravelModelJson;

use Closure;

trait JsonColumnTrait
{
    /**
     * Holds a list of protected columns used by Illuminate\Database\Eloquent\Model.
     *
     * @var array
     */
    private static $protected_columns = [];

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
     * Holds default values for a json attribute.
     *
     * @var array
     */
    //private $json_defaults = [];

    /**
     * Stores options for each JSON attribute.
     *
     * @var array
     */
    //private $json_options = [];

    /**
     * Boot the events that apply which user is making the last event change.
     *
     * @return void
     */
    public static function bootJsonColumnTrait()
    {
        static::$protected_columns = array_keys(get_class_vars(__CLASS__));

        /**
         * Before model is saved, ensure the JSON columns are in a string format.
         */
        static::saving(function ($model) {
            foreach ($model->getJsonColumns() as $column_name) {
                if (is_array($model->$column_name)) {
                    $model->$column_name = json_encode($model->$column_name);
                } elseif (is_object($model->$column_name)) {
                    $model->$column_name = (string) $model->$column_name;
                } else {
                    $model->$column_name = '{}';
                }
            }
        });
    }

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
     * Get the array of columns that this trait should use.
     *
     * @return array
     */
    public function getJsonColumns()
    {
        if (!empty($this->json_columns)) {
            return $this->json_columns;
        }

        return [];
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
                if (!in_array($column_name, static::$protected_columns)) {
                    $this->processJson($column_name, $this->attributes[$column_name]);
                }
            }
        }
    }

    /**
     * Process the json column.
     *
     * @param string $column_name
     * @param mixed  &$value
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function processJson($column_name, &$value)
    {
        if (empty($value) || $value === 'null') {
            $value = [];
        }
        $defaults = (!empty($this->json_defaults[$column_name])) ? $this->json_defaults[$column_name] : [];
        $options = (!empty($this->json_options[$column_name])) ? $this->json_options[$column_name] : [];

        // Only create the json value object if it hasn't been done already.
        if (!isset($this->json_values[$column_name]) || !is_object($this->json_values[$column_name])) {
            $this->json_values[$column_name] = new JsonColumnValue($value, $defaults, $options);
            $json_column_access = function &($column_name) {
                return $this->json_values[$column_name];
            };
            $this->json_methods[$column_name] = Closure::bind($json_column_access, $this, static::class);
        }
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

                return $original;
            }
            $key_array = explode('.', $key, 2);
            if (count($key_array) > 1) {
                list($column_name, $json_key) = $key_array;
                if (array_key_exists($column_name, $this->json_values)) {
                    return $this->json_values[$column_name]->getOriginal($json_key, $default);
                }

                return $default;
            }
        }

        return $original;
    }

    /**
     * Add the change in any json value objects, and if requested
     * provide the columns (dot notation) within the json value objects.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getDirty($include_json = false)
    {
        $dirty = parent::getDirty();
        if (!empty($this->json_values)) {
            foreach ($this->json_values as $column_name => $json_data) {
                if (count($json_data->getDirty())) {
                    $dirty[$column_name] = (string) $json_data;
                } else {
                    unset($dirty[$column_name]);
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
                $this->attributes[$column_name] = (string) $this->json_values[$column_name];
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
