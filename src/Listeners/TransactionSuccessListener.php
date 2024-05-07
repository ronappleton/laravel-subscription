<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Listeners;

use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Events\TransactionSuccessEvent;
use Appleton\Subscriptions\Models\Subscription;

class TransactionSuccessListener
{
    public function handle(TransactionSuccessEvent $event): void
    {
        $subscription = $event->getSubscription();

        if ($subscription->status !== Status::SUSPENDED) {
            return;
        }

        $subscription->resume();
    }
}
