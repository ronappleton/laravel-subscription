<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Observers;

use Appleton\Subscriptions\Events\SubscriptionActivatedEvent;
use Appleton\Subscriptions\Events\SubscriptionCancelledEvent;
use Appleton\Subscriptions\Events\SubscriptionCreatedEvent;
use Appleton\Subscriptions\Events\SubscriptionEndedEvent;
use Appleton\Subscriptions\Events\SubscriptionPausedEvent;
use Appleton\Subscriptions\Events\SubscriptionSuspendedEvent;
use Appleton\Subscriptions\Models\Subscription;

class SubscriptionObserver
{
    public function created(Subscription $subscription): void
    {
        event(new SubscriptionCreatedEvent($subscription));
    }

    public function updating(Subscription $subscription): bool
    {
        if (!$subscription->isDirty('status')) {
            return true;
        }

        if ($subscription->status->value === 'paused' && !$subscription->canPause())
        {
            return false;
        }


        return true;
    }

    public function updated(Subscription $subscription): void
    {
        if ($subscription->isDirty('status')) {
            event(
                match ($subscription->status->value) {
                    'suspended' => new SubscriptionSuspendedEvent($subscription),
                    'cancelled' => new SubscriptionCancelledEvent($subscription),
                    'ended' => new SubscriptionEndedEvent($subscription),
                    'paused' => new SubscriptionPausedEvent($subscription),
                    'active' => new SubscriptionActivatedEvent($subscription),
                }
            );
        }
    }
}
