<?php

declare(strict_types=1);

return [
    'dispatch_now' => true,
    'user_model' => 'App\Models\User',

    'table_names' => [
        'subscriptions' => 'subscriptions',
        'subscription_logs' => 'subscription_logs',
        'subscription_profiles' => 'subscription_profiles',
    ],
];
