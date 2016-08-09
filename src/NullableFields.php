<?php

namespace Iatstuti\Database\Support;

/**
 * Nullable (database) fields trait.
 *
 * Include this trait in any Eloquent models you wish to automatically set
 * empty field values to null on. When saving, iterate over the model's
 * attributes and if their value is empty, make it null before save.
 *
 * @copyright  2015 IATSTUTI
 * @author     Michael Dyrynda <michael@dyrynda.com.au>
 */
trait NullableFields
{
    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    abstract public function getAttributes();


    /**
     * Determine whether a value is JSON castable for inbound manipulation.
     *
     * @param  string  $key
     *
     * @return bool
     */
    abstract protected function isJsonCastable($key);


    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    abstract public function hasSetMutator($key);


    /**
     * Boot the trait, add a saving observer.
     *
     * When saving the model, we iterate over its attributes and for any attribute
     * marked as nullable whose value is empty, we then set its value to null.
     */
    protected static function bootNullableFields()
    {
        static::saving(function ($model) {
            $model->setNullableFields();
        });
    }


    /**
     * Set empty nullable fields to null.
     *
     * @since  1.1.0
     *
     * @return void
     */
    protected function setNullableFields()
    {
        foreach ($this->nullableFromArray($this->getAttributes()) as $key => $value) {
            $this->attributes[$key] = $this->nullIfEmpty($value, $key);
        }
    }


    /**
     * If value is empty, return null, otherwise return the original input.
     *
     * @param  string $value
     * @param  null $key
     *
     * @return null|string
     */
    public function nullIfEmpty($value, $key = null)
    {
        if (! is_null($key) && $this->isJsonCastable($key)) {
            $value  = $this->hasSetMutator($key) ? $value : $this->getJsonCastValue($value);

            return (empty($value) || trim($value) === '') ? null : $value;
        }

        if (is_array($value)) {
            return empty($value) ? null : $value;
        }

        return trim($value) === '' ? null : $value;
    }


    /**
     * Get the nullable attributes of a given array.
     *
     * @param  array $attributes
     *
     * @return array
     */
    protected function nullableFromArray(array $attributes = [])
    {
        if (is_array($this->nullable) && count($this->nullable) > 0) {
            return array_intersect_key($attributes, array_flip($this->nullable));
        }

        // Assume no fields are nullable
        return [];
    }


    /**
     * Return value of the native PHP type as a json-encoded value
     *
     * @param  mixed $value
     *
     * @return string
     */
    private function getJsonCastValue($value)
    {
        return method_exists($this, 'fromJson') ? $this->fromJson($value) : json_decode($value);
    }
}
