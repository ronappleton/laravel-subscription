<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Command;

use Appleton\Subscriptions\Contracts\SubscriptionProcessor;
use Tests\TestCase;

class ProcessSubscriptionsCommandTest extends TestCase
{
    public function testProcessSubscriptionsCommand(): void
    {
        $processor = \Mockery::mock(SubscriptionProcessor::class);
        $processor->shouldReceive('process')->once();

        $this->app->instance(SubscriptionProcessor::class, $processor);

        $this->artisan('subscriptions:process')
            ->assertExitCode(0);
    }
}