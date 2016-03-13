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

    public function getCustomGetAttribute()
    {
        return 'custom getter result';
    }

    public function setCustomSetAttribute($value)
    {
        $this->setJsonAttribute($this->jsonAttributes['custom_set'], 'custom_set', "custom {$value}");
    }
}
