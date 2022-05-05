<?php
/**
 * Define a locale string field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2022
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Laramore\Contracts\Eloquent\LaramoreBuilder;
use Laramore\Contracts\Field\Constraint\IndexableField;
use Laramore\Contracts\Field\ExtraField;
use Laramore\Contracts\Field\Field;
use Laramore\Elements\OperatorElement;
use Laramore\Traits\Field\IndexableConstraints;

class LocaleString extends BaseComposed implements ExtraField, IndexableField
{
    use IndexableConstraints;

    public static function of(string $class)
    {
        return parent::field([], array_fill_keys(config('app.locales'), $class));
    }

    /**
     * Create a field.
     *
     * @param  string             $name
     * @param  array|string|Field $fieldData
     * @return Field
     */
    protected function createField(string $name, $fieldData): Field
    {
        return parent::createField($name, $fieldData)->hidden();
    }

    /**
     * Set the value for the field.
     *
     * @param  LaramoreModel|array|\Illuminate\Contracts\Support\\ArrayAccess $model
     * @param  mixed                                                          $value
     * @return mixed
     */
    public function set($model, $value)
    {
        return parent::set($model, $value) &&
            $this->getOwner()->setFieldValue($this->getField(Lang::getLocale()), $model, $value);
    }

    /**
     * Get the value for the field.
     *
     * @param  LaramoreModel|array|\Illuminate\Contracts\Support\\ArrayAccess $model
     * @return mixed
     */
    public function resolve($model)
    {
        $field = $this->hasField(Lang::getLocale())
            ? $this->getField(Lang::getLocale())
            : $this->getField(Lang::getFallback());

        return $this->getOwner()->getFieldValue($field, $model);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param Field                            $field
     * @param LaramoreModel|array|\ArrayAccess $model
     * @param mixed                            $value
     * @return mixed
     */
    public function setFieldValue(Field $field, $model, $value)
    {
        parent::reset($model);

        return parent::setFieldValue($field, $model, $value);
    }

    /**
     * Dry the value in a simple format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function dry($value)
    {
        return is_null($value) ? $value : (string) $value;
    }

    /**
     * Hydrate the value in a simple format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function hydrate($value)
    {
        return is_null($value) ? $value : (string) $value;
    }

    /**
     * Serialize the value for outputs.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        return $value;
    }

    /**
     * Cast the value in the correct format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value)
    {
        return is_null($value) ? $value : (string) $value;
    }

    /**
     * Add a where null condition from this field.
     *
     * @param  LaramoreBuilder $builder
     * @param  string          $boolean
     * @param  boolean         $not
     * @return LaramoreBuilder
     */
    public function whereNull(LaramoreBuilder $builder, string $boolean='and', bool $not=false): LaramoreBuilder
    {
        $builder = $this->getField('firstname')->whereNull($builder, $boolean, $not);

        return $this->getField('lastname')->whereNull($builder, $boolean, $not);
    }

    /**
     * Add a where not null condition from this field.
     *
     * @param  LaramoreBuilder $builder
     * @param  string          $boolean
     * @return LaramoreBuilder
     */
    public function whereNotNull(LaramoreBuilder $builder, string $boolean='and'): LaramoreBuilder
    {
        return $this->whereNull($builder, $boolean, true);
    }

    /**
     * Add a where in condition from this field.
     *
     * @param  LaramoreBuilder    $builder
     * @param  Collection $value
     * @param  string             $boolean
     * @param  boolean            $notIn
     * @return LaramoreBuilder
     */
    public function whereIn(LaramoreBuilder $builder, Collection $value=null,
                            string $boolean='and', bool $notIn=false): LaramoreBuilder
    {
        $operator = $notIn ? Operator::equal() : Operator::different();

        return $builder->where(function ($builder) use ($value, $notIn, $operator) {
            foreach ($value as $name) {
                [$lastname, $firstname] = $this->split($name);

                $builder->where(function ($subBuilder) use ($lastname, $firstname, $operator) {
                    $this->getField('lastname')->where($subBuilder, $operator, $lastname, 'and');
                    $this->getField('firstname')->where($subBuilder, $operator, $firstname, 'and');
                }, $notIn ? 'and' : 'or');
            }
        }, $boolean);
    }

    /**
     * Add a where not in condition from this field.
     *
     * @param  LaramoreBuilder    $builder
     * @param  Collection $value
     * @param  string             $boolean
     * @return LaramoreBuilder
     */
    public function whereNotIn(LaramoreBuilder $builder, Collection $value=null, string $boolean='and'): LaramoreBuilder
    {
        return $this->whereIn($builder, $value, $boolean, true);
    }

    /**
     * Add a where condition from this field.
     *
     * @param  LaramoreBuilder $builder
     * @param  OperatorElement $operator
     * @param  mixed           $value
     * @param  string          $boolean
     * @return LaramoreBuilder
     */
    public function where(LaramoreBuilder $builder, OperatorElement $operator,
                          $value=null, string $boolean='and'): LaramoreBuilder
    {
        [$lastname, $firstname] = $this->split($value);

        return $builder->where(function ($subBuilder) use ($operator, $lastname, $firstname) {
            $this->getField('lastname')->where($subBuilder, $operator, $lastname, 'and');
            $this->getField('firstname')->where($subBuilder, $operator, $firstname, 'and');
        }, $boolean);
    }
}
