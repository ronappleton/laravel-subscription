<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Subscription;

use Appleton\Subscriptions\Enums\PaymentStatus;
use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Models\Subscription;
use Appleton\Subscriptions\Models\SubscriptionLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;

class ForRetryScopeTest extends TestCase
{
    use DatabaseMigrations;

    public function testForRetryScopeShouldFindARetryNeeded(): void
    {
        Event::fake();

        $now = Carbon::parse('2020-12-11');
        TestTime::freeze($now);

        $subscription = Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'max_retries' => 3,
            'retry_frequency_days' => 3,
            'fixed_day_of_month' => 7,
        ]);

        SubscriptionLog::create([
            'uuid' => Str::uuid(),
            'subscription_id' => $subscription->id,
            'amount' => 10.00,
            'status' => PaymentStatus::UNPAID,
            'created_at' => $now->copy()->subDays($subscription->retry_frequency_days),
        ]);

        $subscriptions = Subscription::forRetry()->get();

        $this->assertCount(1, $subscriptions);
    }

    public function testForRetryScopeShouldNotFindARetryNeededAsWeAreOneDayPriorToTheRetryFrequency(): void
    {
        Event::fake();

        $now = Carbon::parse('2020-12-11');
        TestTime::freeze($now);

        $subscription = Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'max_retries' => 3,
            'retry_frequency_days' => 3,
            'fixed_day_of_month' => 7,
        ]);

        SubscriptionLog::create([
            'uuid' => Str::uuid(),
            'subscription_id' => $subscription->id,
            'amount' => 10.00,
            'status' => PaymentStatus::UNPAID,
            'created_at' => $now->copy()->subDays($subscription->retry_frequency_days - 1),
        ]);

        $subscriptions = Subscription::forRetry()->get();

        $this->assertCount(0, $subscriptions);
    }

    public function testForRetryScopeShouldNotFindARetryNeededAsWeAreOneDayPostRetryFrequency(): void
    {
        Event::fake();

        $now = Carbon::parse('2020-12-11');
        TestTime::freeze($now);

        $subscription = Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'max_retries' => 3,
            'retry_frequency_days' => 3,
            'fixed_day_of_month' => 7,
        ]);

        SubscriptionLog::create([
            'uuid' => Str::uuid(),
            'subscription_id' => $subscription->id,
            'amount' => 10.00,
            'status' => PaymentStatus::UNPAID,
            'created_at' => $now->copy()->subDays($subscription->retry_frequency_days + 1),
        ]);

        $subscriptions = Subscription::forRetry()->get();

        $this->assertCount(0, $subscriptions);
    }

    public function testForRetryScopeShouldNotFindARetryNeededBecauseTheSubscriptionWasPaid(): void
    {
        Event::fake();

        $now = Carbon::parse('2020-12-11');
        TestTime::freeze($now);

        $subscription = Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'max_retries' => 3,
            'retry_frequency_days' => 3,
            'fixed_day_of_month' => 7,
        ]);

        SubscriptionLog::create([
            'uuid' => Str::uuid(),
            'subscription_id' => $subscription->id,
            'amount' => 10.00,
            'status' => PaymentStatus::PAID,
            'created_at' => $now->copy()->subDays($subscription->retry_frequency_days),
        ]);

        $subscriptions = Subscription::forRetry()->get();

        $this->assertCount(0, $subscriptions);
    }

    public function testForRetryScopeShouldNotFindARetryNeededBecauseTheSubscriptionWasCancelled(): void
    {
        Event::fake();

        $now = Carbon::parse('2020-12-11');
        TestTime::freeze($now);

        $subscription = Subscription::factory()->create([
            'status' => Status::CANCELLED,
            'max_retries' => 3,
            'retry_frequency_days' => 3,
            'fixed_day_of_month' => 7,
        ]);

        SubscriptionLog::create([
            'uuid' => Str::uuid(),
            'subscription_id' => $subscription->id,
            'amount' => 10.00,
            'status' => PaymentStatus::UNPAID,
            'created_at' => $now->copy()->subDays($subscription->retry_frequency_days),
        ]);

        $subscriptions = Subscription::forRetry()->get();

        $this->assertCount(0, $subscriptions);
    }

    public function testForRetryScopeShouldNotFindARetryNeededBecauseTheSubscriptionWasPaused(): void
    {
        Event::fake();

        $now = Carbon::parse('2020-12-11');
        TestTime::freeze($now);

        $subscription = Subscription::factory()->create([
            'status' => Status::PAUSED,
            'max_retries' => 3,
            'retry_frequency_days' => 3,
            'fixed_day_of_month' => 7,
        ]);

        SubscriptionLog::create([
            'uuid' => Str::uuid(),
            'subscription_id' => $subscription->id,
            'amount' => 10.00,
            'status' => PaymentStatus::UNPAID,
            'created_at' => $now->copy()->subDays($subscription->retry_frequency_days),
        ]);

        $subscriptions = Subscription::forRetry()->get();

        $this->assertCount(0, $subscriptions);
    }

    public function testForRetryScopeShouldNotFindARetryBecauseWhilstNotPaidOnTheDayARetryWasSuccessful(): void
    {
        Event::fake();

        $now = Carbon::parse('2020-12-11');
        TestTime::freeze($now);

        $subscription = Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'max_retries' => 3,
            'retry_frequency_days' => 3,
            'fixed_day_of_month' => 7,
        ]);

        SubscriptionLog::create([
            'uuid' => Str::uuid(),
            'subscription_id' => $subscription->id,
            'amount' => 10.00,
            'status' => PaymentStatus::UNPAID,
            'created_at' => Carbon::parse('2020-12-07'),
        ]);

        SubscriptionLog::create([
            'uuid' => Str::uuid(),
            'subscription_id' => $subscription->id,
            'amount' => 10.00,
            'status' => PaymentStatus::PAID,
            'created_at' => Carbon::parse('2020-12-11'),
        ]);

        $subscriptions = Subscription::forRetry()->get();

        $this->assertCount(0, $subscriptions);
    }
    public function testForRetryScopeShouldNotFindARetryBecauseWhilstNotPaidOnTheDayItWasPaidTheDayAfter(): void
    {
        Event::fake();

        $now = Carbon::parse('2020-12-11');
        TestTime::freeze($now);

        $subscription = Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'max_retries' => 3,
            'retry_frequency_days' => 3,
            'fixed_day_of_month' => 7,
        ]);

        SubscriptionLog::create([
            'uuid' => Str::uuid(),
            'subscription_id' => $subscription->id,
            'amount' => 10.00,
            'status' => PaymentStatus::UNPAID,
            'created_at' => Carbon::parse('2020-12-07'),
        ]);

        SubscriptionLog::create([
            'uuid' => Str::uuid(),
            'subscription_id' => $subscription->id,
            'amount' => 10.00,
            'status' => PaymentStatus::PAID,
            'created_at' => Carbon::parse('2020-12-08'),
        ]);

        $subscriptions = Subscription::forRetry()->get();

        $this->assertCount(0, $subscriptions);
    }

    public function testForRetryScopeShouldFindTwoSubscriptionsAndIgnoreOne(): void
    {
        Event::fake();

        $now = Carbon::parse('2020-12-11');
        TestTime::freeze($now);

        $subscription1 = Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'max_retries' => 3,
            'retry_frequency_days' => 3,
            'fixed_day_of_month' => 7,
        ]);

        SubscriptionLog::create([
            'uuid' => Str::uuid(),
            'subscription_id' => $subscription1->id,
            'amount' => 10.00,
            'status' => PaymentStatus::UNPAID,
            'created_at' => $now->copy()->subDays($subscription1->retry_frequency_days),
        ]);

        $subscription2 = Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'max_retries' => 3,
            'retry_frequency_days' => 3,
            'fixed_day_of_month' => 7,
        ]);

        SubscriptionLog::create([
            'uuid' => Str::uuid(),
            'subscription_id' => $subscription2->id,
            'amount' => 10.00,
            'status' => PaymentStatus::UNPAID,
            'created_at' => $now->copy()->subDays($subscription2->retry_frequency_days),
        ]);

        SubscriptionLog::create([
            'uuid' => Str::uuid(),
            'subscription_id' => $subscription2->id,
            'amount' => 10.00,
            'status' => PaymentStatus::PAID,
            'created_at' => $now,
        ]);

        $subscription3 = Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'max_retries' => 3,
            'retry_frequency_days' => 3,
            'fixed_day_of_month' => 7,
        ]);

        SubscriptionLog::create([
            'uuid' => Str::uuid(),
            'subscription_id' => $subscription3->id,
            'amount' => 10.00,
            'status' => PaymentStatus::UNPAID,
            'created_at' => $now->copy()->subDays($subscription3->retry_frequency_days),
        ]);

        $subscriptions = Subscription::forRetry()->get();

        $this->assertCount(2, $subscriptions);
        $this->assertTrue($subscriptions->contains($subscription1));
        $this->assertFalse($subscriptions->contains($subscription2));
        $this->assertTrue($subscriptions->contains($subscription3));
    }
}