<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Jobs;

use Appleton\Subscriptions\Models\Subscription;
use Illuminate\Bus\Queueable;
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

    public function handle(): void
    {
        $this->subscription->getAction()->handle($this->subscription);
    }
}
