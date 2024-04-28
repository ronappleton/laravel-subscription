<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Observers;

use Appleton\Subscriptions\Events\TransactionFailedEvent;
use Appleton\Subscriptions\Events\TransactionSuccessEvent;
use Appleton\Subscriptions\Exceptions\SubscriptionLog as SubscriptionLogException;
use Appleton\Subscriptions\Models\Subscription;

class SubscriptionLogObserver
{
    /**
     * @throws SubscriptionLogException
     */
    public function created(Subscription $subscription): void
    {
        match ($subscription->getAttribute('status')) {
            'failed' => event(new TransactionFailedEvent($subscription)),
            'success' => event(new TransactionSuccessEvent($subscription)),
            default => SubscriptionLogException::InvalidStatus($subscription->getAttribute('status')),
        };
    }
}
