<?php

namespace Laramore\Fields;

$localeTemplates = [];

foreach (config('app.locales') as $locale) {
    $localeTemplates[$locale] = '${name}_'.$locale;
}

return [

    /*
    |--------------------------------------------------------------------------
    | Default text fields
    |--------------------------------------------------------------------------
    |
    | This option defines the default text fields.
    |
    */

    LocaleString::class => [
        'templates' => $localeTemplates,
    ],

];
