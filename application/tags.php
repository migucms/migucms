<?php

return [
    'app_init' => [
        'app\\common\\behavior\\Init',
    ],
    'module_init' => [
        'app\\common\\behavior\\Base',
    ],
    'action_begin' => [
        'app\\common\\behavior\\Hook',
    ],
    'view_filter' => [
    ],
    'log_write' => [
    ],
    'app_end' => [
    ],
    'system_admin_index' => [
        'plugins\\mgcms\\mgcms',
    ],
    'send_sms' => [
        'plugins\\sms\\sms',
    ],
    'send_code' => [
        'plugins\\sms\\sms',
    ],
    'check_code' => [
        'plugins\\sms\\sms',
    ],
];
