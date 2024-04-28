<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Processors;

use Appleton\Subscriptions\Contracts\SubscriptionProcessor;
use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Jobs\ProcessSubscriptionJob;
use Appleton\Subscriptions\Models\Subscription;

class Processor implements SubscriptionProcessor
{
    public function processWarnings(): void
    {
        Subscription::forWarning()
            ->chunk(100, function ($subscriptions) {
            $subscriptions->each(function (Subscription $subscription) {
                ProcessSubscriptionJob::dispatch($subscription);
            });
        });
    }

    public function process(): void
    {
        /**
         * When we process subscriptions, we record in a transaction log
         * the result of the transaction. This can then be used to determine
         * if we should retry the transaction or not.
         * So we should get the latest log for the subscription and check if
         * we should retry the transaction or not.
         */
        $dispatchNow = config('subscriptions.dispatch_now', false);

        $subscriptions = Subscription::forDay()->status(Status::ACTIVE)->get();

        $subscriptions->each(function (Subscription $subscription) use ($dispatchNow) {
            if ($dispatchNow) {
                ProcessSubscriptionJob::dispatchSync($subscription);
            } else {
                ProcessSubscriptionJob::dispatch($subscription);
            }
        });

    }

    public function processRetries(): void
    {
        /**
         * Subscription has a transaction log that is 'retry_frequency_days' old
         * and has a status of failed and retry_count < max_retries
         */
        $subscriptions = Subscription::forRetry()->status(Status::ACTIVE)->get();

        $subscriptions->each(function (Subscription $subscription) {
            ProcessSubscriptionJob::dispatch($subscription);
        });
    }
}