<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Observers;

use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Events\SubscriptionActivatedEvent;
use Appleton\Subscriptions\Events\SubscriptionCancelledEvent;
use Appleton\Subscriptions\Events\SubscriptionCreatedEvent;
use Appleton\Subscriptions\Events\SubscriptionEndedEvent;
use Appleton\Subscriptions\Events\SubscriptionPausedEvent;
use Appleton\Subscriptions\Events\SubscriptionResumedEvent;
use Appleton\Subscriptions\Events\SubscriptionSuspendedEvent;
use Appleton\Subscriptions\Events\SubscriptionUnsuspendedEvent;
use Appleton\Subscriptions\Models\Subscription;

class SubscriptionObserver
{
    public function created(Subscription $subscription): void
    {
        event(new SubscriptionCreatedEvent($subscription));
    }

    public function updated(Subscription $subscription): void
    {
        if ($subscription->isDirty('status')) {
            event(
                match ($subscription->status) {
                    Status::SUSPENDED => new SubscriptionSuspendedEvent($subscription),
                    Status::CANCELLED => new SubscriptionCancelledEvent($subscription),
                    Status::ENDED => new SubscriptionEndedEvent($subscription),
                    Status::PAUSED => new SubscriptionPausedEvent($subscription),
                    Status::ACTIVE => $this->getActiveEvent($subscription),
                    default => null,
                }
            );
        }
    }

    private function getActiveEvent(
        Subscription $subscription
    ): SubscriptionActivatedEvent|SubscriptionResumedEvent|SubscriptionUnsuspendedEvent {
        return match($subscription->getOriginal('status')) {
            Status::PAUSED => new SubscriptionResumedEvent($subscription),
            Status::SUSPENDED => new SubscriptionUnsuspendedEvent($subscription),
            default => new SubscriptionActivatedEvent($subscription),
        };
    }
}
