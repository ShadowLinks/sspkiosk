<?php

return [

    'student_domain' => env('STUDENT_GOOGLE_DOMAIN'),

    'allowed_student_org_units' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('ALLOWED_STUDENT_ORG_UNITS', ''))
    ))),

    'blocked_staff_org_units' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('BLOCKED_STAFF_ORG_UNITS', ''))
    ))),

    'oauth' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
    ],

    'service_account_json_path' => env('GOOGLE_SERVICE_ACCOUNT_JSON_PATH'),

    'admin_impersonation_email' => env('GOOGLE_ADMIN_IMPERSONATION_EMAIL'),

    'directory_scopes' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('GOOGLE_DIRECTORY_SCOPES', 'https://www.googleapis.com/auth/admin.directory.user'))
    ))),

];
