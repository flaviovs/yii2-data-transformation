<?php

namespace fv\yii\behaviors;

abstract class DataTransformation extends \yii\base\Behavior
{
    public $attributes = [];

    public $autoConvertNull = true;

    abstract protected function transform($value);


    protected function transformBack($value)
    {
        return $value === null ? null : (string)$value;
    }


    public function attach($owner)
    {
        if (!$this->attributes) {
            throw new \yii\base\InvalidConfigException('"attributes" not set');
        }

        parent::attach($owner);
    }


    public function __set($name, $value)
    {
        if (!isset($this->attributes[$name])) {
            return parent::__set($name, $value);
        }

        $attribute = $this->attributes[$name];

        $this->owner->$attribute = ($value === null && $this->autoConvertNull)
            ? null
            : $this->transformBack($value);

        return $this->owner->$attribute;
    }


    public function __get($name)
    {
        if (!isset($this->attributes[$name])) {
            return parent::__get($name);
        }

        $attribute = $this->attributes[$name];

        return ($this->owner->$attribute === null && $this->autoConvertNull)
            ? null
            : $this->transform($this->owner->$attribute);
    }


    public function canSetProperty($name, $checkVars = true)
    {
        return isset($this->attributes[$name]);
    }


    public function canGetProperty($name, $checkVars = true)
    {
        return isset($this->attributes[$name]);
    }

}
