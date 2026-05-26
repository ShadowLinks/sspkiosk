<?php

return [

    'headers_enabled' => filter_var(env('SECURITY_HEADERS_ENABLED', true), FILTER_VALIDATE_BOOL),

    'rate_limits' => [
        'admin_login' => [
            'max_attempts' => (int) env('RATE_LIMIT_ADMIN_LOGIN', 5),
            'decay_minutes' => (int) env('RATE_LIMIT_ADMIN_LOGIN_DECAY', 1),
        ],
        'kiosk_reset_lookup' => [
            'max_attempts' => (int) env('RATE_LIMIT_KIOSK_RESET_LOOKUP', 20),
            'decay_minutes' => (int) env('RATE_LIMIT_KIOSK_RESET_LOOKUP_DECAY', 1),
        ],
        'kiosk_enroll' => [
            'max_attempts' => (int) env('RATE_LIMIT_KIOSK_ENROLL', 10),
            'decay_minutes' => (int) env('RATE_LIMIT_KIOSK_ENROLL_DECAY', 1),
        ],
        'slack_interactions' => [
            'max_attempts' => (int) env('RATE_LIMIT_SLACK_INTERACTIONS', 60),
            'decay_minutes' => (int) env('RATE_LIMIT_SLACK_INTERACTIONS_DECAY', 1),
        ],
    ],

];
