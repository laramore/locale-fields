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
use Laramore\Elements\OperatorElement;

class LocaleString extends BaseComposed
{
    public static function of(string $class)
    {
        return parent::field([], array_fill_keys(config('app.locales'), $class));
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
