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

use Illuminate\Support\Facades\Lang;
use Laramore\Contracts\Field\ComposedField;

class LocaleString extends ComposedField
{
    public static function field(string $class)
    {
        return parent::field([], array_fill_keys(config('app.locales'), $class));
    }

    /**
     * Serialize the value for outputs.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        return Lang::get($value);
    }
}
