<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Listeners;

use Appleton\Subscriptions\Events\TransactionFailedEvent;
use Appleton\Subscriptions\Models\Subscription;

class TransactionFailedListener
{
    private Subscription $subscription;

    public function handle(TransactionFailedEvent $event): void
    {
        $this->subscription = $event->getSubscription();

        if ($this->maxRetries() === 0 || $this->maxedRetriesReached()) {
            $this->subscription->suspend();
        }
    }

    protected function maxedRetriesReached(): bool
    {
        return $this->getFailedSubscriptionLogCount() === $this->maxRetries();
    }

    protected function getFailedSubscriptionLogCount(): int
    {
        return $this->subscription->failedPaymentsLastMonth()->count();
    }

    protected function maxRetries(): int
    {
        /** @var int $maxRetries */
        $maxRetries = $this->subscription->getAttribute('max_retries');

        return $maxRetries;
    }
}
