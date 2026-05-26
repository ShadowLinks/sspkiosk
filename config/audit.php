<?php

return [

    'retention_days' => (int) env('AUDIT_LOG_RETENTION_DAYS', 365),

    'enabled' => filter_var(env('AUDIT_LOG_ENABLED', true), FILTER_VALIDATE_BOOL),

];
