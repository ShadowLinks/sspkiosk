<?php

return [

    'route_prefix' => env('ADMIN_ROUTE_PREFIX', 'admin'),

    'allowed_emails' => array_values(array_filter(array_map(
        'strtolower',
        array_map('trim', explode(',', env('ADMIN_ALLOWED_EMAILS', '')))
    ))),

];
