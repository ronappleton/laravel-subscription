<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Listeners;

use Appleton\Subscriptions\Events\SubscriptionSuspendedEvent;
use Appleton\Subscriptions\Events\TransactionFailedEvent;
use Appleton\Subscriptions\Models\Subscription;
use Illuminate\Support\Collection;

class TransactionFailedListener
{
    private Subscription $subscription;

    public function handle(TransactionFailedEvent $event): void
    {
        $this->subscription = $event->getSubscription();

        if ($this->maxRetries() === 0 || $this->maxedRetriesReached()) {
            $this->subscription->setAttribute('status', 'suspended');
            $this->subscription->save();
        }
    }

    protected function maxedRetriesReached(): bool
    {
        return $this->getSubscriptionLogs($this->subscription)
                ->map(function ($log) {
                    return $log->getAttribute('result');
                })->filter(fn(string $result) => $result === 'failed'
                )->count() === $this->maxRetries();
    }

    protected function getSubscriptionLogs(Subscription $subscription): Collection
    {
        return $subscription->logs()
            ->orderBy('created_at', 'desc')
            ->take($subscription->getAttribute('max_retries') + 1)
            ->get();
    }

    protected function maxRetries(): int
    {
        return $this->subscription->getAttribute('max_retries');
    }
}
