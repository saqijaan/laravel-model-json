<?php

class MockJsonModel extends Illuminate\Database\Eloquent\Model
{
    use \ModelJsonColumn\JsonColumnTrait;

    protected $json_columns;

    public function __construct(array $attributes = [])
    {
        static::$booted[get_class($this)] = true;
        parent::__construct($attributes);
    }

    public function setJsonColumns(array $columns)
    {
        $this->json_columns = $columns;
    }

    public function setCastsColumns(array $columns)
    {
        $this->casts = $columns;
    }

    public function setJsonColumnDefaults($column_name, $defaults)
    {
        $this->json_defaults[$column_name] = $defaults;
    }
}
