<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Listeners;

use Appleton\Subscriptions\Events\TransactionSuccessEvent;
use Appleton\Subscriptions\Models\Subscription;

class TransactionSuccessListener
{
    private Subscription $subscription;

    public function __construct()
    {
    }

    public function handle(TransactionSuccessEvent $event): void
    {
        $this->subscription = $event->getSubscription();

        $this->subscription->setAttribute('status', 'active');
        $this->subscription->save();
    }
}
