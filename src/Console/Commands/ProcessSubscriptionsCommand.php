<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Console\Commands;

use Appleton\Subscriptions\Contracts\SubscriptionProcessor;
use Illuminate\Console\Command;

class ProcessSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:process';

    protected $description = 'Run the subscription processor.';

    public function __construct(private readonly SubscriptionProcessor $processor)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->processor->process();
    }
}
