<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Contracts;

use Appleton\Subscriptions\Models\Subscription;

interface SubscriptionAction
{
    public function handle(Subscription $subscription): bool;
}