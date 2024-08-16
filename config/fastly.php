<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Surrogate Keys
    |--------------------------------------------------------------------------
    |
    | List of surrogate keys that can be used to target content for purging.
    |
    | https://www.fastly.com/documentation/reference/http/http-headers/Surrogate-Key/
    |
    */

    'surrogate_keys' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Surrogate Key
    |--------------------------------------------------------------------------
    |
    | The default surrogate key to use when no path matches are found.
    |
    */

    'default_surrogate_key' => 'pages',

];
