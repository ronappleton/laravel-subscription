<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Jobs;

use Appleton\Subscriptions\Events\TransactionFailedEvent;
use Appleton\Subscriptions\Events\TransactionSuccessEvent;
use Appleton\Subscriptions\Exceptions\SubscriptionAction;
use Appleton\Subscriptions\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Appleton\Subscriptions\Jobs\Contracts\ProcessSubscriptionJob as ProcessSubscriptionJobContract;

class ProcessSubscriptionJob implements ProcessSubscriptionJobContract, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Subscription $subscription)
    {
    }

    /**
     * @throws SubscriptionAction
     * @throws BindingResolutionException
     */
    public function handle(): void
    {
        event(
            match($this->subscription->getAction()->handle($this->subscription))
            {
                false => new TransactionFailedEvent($this->subscription),
                default => new TransactionSuccessEvent($this->subscription),
            }
        );
    }
}
