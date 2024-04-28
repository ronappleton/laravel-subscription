<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Events;

use Appleton\Subscriptions\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;

class SubscriptionCreatedEvent
{
    use Dispatchable;

    public function __construct(private readonly Subscription $subscription)
    {
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }
}
