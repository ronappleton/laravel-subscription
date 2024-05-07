<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Observers;

use Appleton\Subscriptions\Enums\PaymentStatus;
use Appleton\Subscriptions\Events\TransactionFailedEvent;
use Appleton\Subscriptions\Events\TransactionSuccessEvent;
use Appleton\Subscriptions\Models\SubscriptionLog;

class SubscriptionLogObserver
{
    public function created(SubscriptionLog $subscriptionLog): void
    {
        match ($subscriptionLog->getAttribute('status')) {
            PaymentStatus::UNPAID => event(new TransactionFailedEvent($subscriptionLog->subscription)),
            default => event(new TransactionSuccessEvent($subscriptionLog->subscription)),
        };
    }
}
