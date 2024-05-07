<?php

declare(strict_types=1);

namespace Tests\Feature\Processor;

use Appleton\Subscriptions\Enums\PaymentStatus;
use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Jobs\ProcessSubscriptionJob;
use Appleton\Subscriptions\Models\Subscription;
use Appleton\Subscriptions\Models\SubscriptionLog;
use Appleton\Subscriptions\Processors\Processor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Queue;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class ProcessorTest extends TestCase
{
    use DatabaseMigrations;

    public function testWarningsAreProcessed(): void
    {
        TestTime::freeze(Carbon::parse('2021-01-01'));

        Queue::fake();

        Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'advanced_warning_days' => 3,
            'fixed_day_of_month' => 4,
        ]);

        $processor = new Processor();

        $processor->processWarnings();

        Queue::assertPushed(ProcessSubscriptionJob::class);
    }

    public function testForDayAreProcessed(): void
    {
        TestTime::freeze(Carbon::parse('2021-01-01'));

        Queue::fake();

        Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'fixed_day_of_month' => 1,
        ]);

        Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'fixed_day_of_month' => 2,
        ]);

        Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'fixed_day_of_month' => 3,
        ]);

        $processor = new Processor();

        $processor->process();

        Queue::assertPushed(ProcessSubscriptionJob::class, 1);
    }

    public function testRetriesAreProcessed(): void
    {
        TestTime::freeze(Carbon::parse('2020-12-10'));

        Queue::fake();

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'retry_frequency_days' => 3,
            'max_retries' => 3,
            'fixed_day_of_month' => 7,
        ]);

        SubscriptionLog::create([
            'subscription_id' => $subscription->id,
            'amount' => '10.00',
            'status' => PaymentStatus::UNPAID,
            'created_at' => Carbon::parse('2020-12-7'),
        ]);

        $processor = new Processor();

        $processor->processRetries();

        Queue::assertPushed(ProcessSubscriptionJob::class, 1);
    }

    public function testDelayedDispatch(): void
    {
        TestTime::freeze(Carbon::parse('2021-01-01'));

        config(['subscriptions.dispatch_now' => false]);

        Queue::fake();

        Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'fixed_day_of_month' => 1,
        ]);

        Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'fixed_day_of_month' => 2,
        ]);

        Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'fixed_day_of_month' => 3,
        ]);

        $processor = new Processor();

        $processor->process();

        Queue::assertPushed(ProcessSubscriptionJob::class, 1);
    }
}