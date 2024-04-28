<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Observers;

use Appleton\Subscriptions\Events\TransactionFailedEvent;
use Appleton\Subscriptions\Events\TransactionSuccessEvent;
use Appleton\Subscriptions\Exceptions\SubscriptionLog as SubscriptionLogException;
use Appleton\Subscriptions\Models\SubscriptionLog;

class SubscriptionLogObserver
{
    /**
     * @throws SubscriptionLogException
     */
    public function created(SubscriptionLog $subscriptionLog): void
    {
        match ($subscriptionLog->getAttribute('status')) {
            'failed' => event(new TransactionFailedEvent($subscriptionLog)),
            'success' => event(new TransactionSuccessEvent($subscriptionLog)),
            default => SubscriptionLogException::InvalidStatus($subscriptionLog->getAttribute('status')),
        };
    }
}
