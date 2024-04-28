<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Listeners;

use Appleton\Subscriptions\Events\SubscriptionPausedEvent;

class SubscriptionPausedListener
{
    public function __construct()
    {
    }

    public function handle(SubscriptionPausedEvent $event): void
    {
        
    }
}
