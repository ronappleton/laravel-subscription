<?php

declare(strict_types=1);

namespace Tests\Feature\Events;

use Appleton\Subscriptions\Enums\PaymentStatus;
use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Events\SubscriptionActivatedEvent;
use Appleton\Subscriptions\Events\SubscriptionCancelledEvent;
use Appleton\Subscriptions\Events\SubscriptionCreatedEvent;
use Appleton\Subscriptions\Events\SubscriptionEndedEvent;
use Appleton\Subscriptions\Events\SubscriptionPausedEvent;
use Appleton\Subscriptions\Events\SubscriptionResumedEvent;
use Appleton\Subscriptions\Events\SubscriptionSuspendedEvent;
use Appleton\Subscriptions\Events\SubscriptionUnsuspendedEvent;
use Appleton\Subscriptions\Events\TransactionFailedEvent;
use Appleton\Subscriptions\Events\TransactionSuccessEvent;
use Appleton\Subscriptions\Exceptions\SubscriptionAction;
use Appleton\Subscriptions\Contracts\SubscriptionAction as SubscriptionActionContract;
use Appleton\Subscriptions\Jobs\ProcessSubscriptionJob;
use Appleton\Subscriptions\Models\Subscription;
use Appleton\Subscriptions\Models\SubscriptionLog;
use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\Mock;
use PHPUnit\Framework\MockObject\Exception;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class EventsTest extends TestCase
{
    use DatabaseMigrations;

    public function testCanGetSubscriptionFromActivatedEvent(): void
    {
        Event::fake(SubscriptionActivatedEvent::class);

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'status' => Status::INACTIVE,
        ]);

        $subscription->activate();

        Event::assertDispatched(SubscriptionActivatedEvent::class, function ($e) use ($subscription) {
            return $e->getSubscription()->is($subscription);
        });
    }

    public function testCanGetSubscriptionFromCancelledEvent(): void
    {
        Event::fake(SubscriptionCancelledEvent::class);

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'allow_cancel' => true,
            'status' => Status::ACTIVE,
            'cancelled_at' => null,
            'activated_at' => now(),
        ]);

        $subscription->cancel();

        Event::assertDispatched(SubscriptionCancelledEvent::class, function ($e) use ($subscription) {
            return $e->getSubscription()->is($subscription);
        });
    }

    public function testCanGetSubscriptionFromEndedEvent(): void
    {
        Event::fake(SubscriptionEndedEvent::class);

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'activated_at' => now(),
        ]);

        $subscription->end();

        Event::assertDispatched(SubscriptionEndedEvent::class, function ($e) use ($subscription) {
            return $e->getSubscription()->is($subscription);
        });
    }

    public function testCanGetSubscriptionFromPausedEvent(): void
    {
        Event::fake(SubscriptionPausedEvent::class);

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'allow_pause' => true,
            'status' => Status::ACTIVE,
            'activated_at' => now(),
            'ended_at' => null,
            'paused_at' => null,
        ]);

        $subscription->pause();

        Event::assertDispatched(SubscriptionPausedEvent::class, function ($e) use ($subscription) {
            return $e->getSubscription()->is($subscription);
        });
    }

    public function testCanGetSubscriptionFromSuspendedEvent(): void
    {
        Event::fake(SubscriptionSuspendedEvent::class);

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'activated_at' => now(),
        ]);

        $subscription->suspend();

        Event::assertDispatched(SubscriptionSuspendedEvent::class, function ($e) use ($subscription) {
            return $e->getSubscription()->is($subscription);
        });
    }

    public function testCanGetSubscriptionFromResumedEvent(): void
    {
        Event::fake(SubscriptionResumedEvent::class);

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'status' => Status::PAUSED,
            'activated_at' => now(),
            'paused_at' => now(),
            'resumed_at' => null,
        ]);

        $subscription->resume();

        Event::assertDispatched(SubscriptionResumedEvent::class, function ($e) use ($subscription) {
            return $e->getSubscription()->is($subscription);
        });
    }

    public function testCanGetSubscriptionFromUnsuspendedEvent(): void
    {
        Event::fake(SubscriptionUnsuspendedEvent::class);

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'status' => Status::SUSPENDED,
            'activated_at' => now(),
            'suspended_at' => now(),
            'resumed_at' => null,
        ]);

        $subscription->resume();

        Event::assertDispatched(SubscriptionUnsuspendedEvent::class, function ($e) use ($subscription) {
            return $e->getSubscription()->is($subscription);
        });
    }

    public function testCanGetSubscriptionFromSubscriptionCreatedEvent(): void
    {
        Event::fake(SubscriptionCreatedEvent::class);

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create();

        Event::assertDispatched(SubscriptionCreatedEvent::class, function ($e) use ($subscription) {
            return $e->getSubscription()->is($subscription);
        });
    }

    public function testCanGetSubscriptionFromTransactionFailedEvent(): void
    {
        Event::fake(TransactionFailedEvent::class);

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create();

        event(new TransactionFailedEvent($subscription));

        Event::assertDispatched(TransactionFailedEvent::class, function ($e) use ($subscription) {
            return $e->getSubscription()->is($subscription);
        });
    }

    /**
     * @throws SubscriptionAction
     * @throws BindingResolutionException
     */
    public function testCanGetSubscriptionFromTransactionSuccessEvent(): void
    {
        Event::fake(TransactionSuccessEvent::class);

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create();

        event(new TransactionSuccessEvent($subscription));

        Event::assertDispatched(TransactionSuccessEvent::class, function ($e) use ($subscription) {
            return $e->getSubscription()->is($subscription);
        });
    }

    /**
     * @throws BindingResolutionException
     * @throws Exception
     * @throws SubscriptionAction
     */
    public function testCanGetSubscriptionFromTransactionSuccessEventWhenSubscriptionLogIsCreated(): void
    {
        Event::fake(TransactionSuccessEvent::class);

        TestTime::freeze(Carbon::parse('2021-01-01 00:00:00'));

        $subscriptionActionMock = $this->createMock(SubscriptionActionContract::class);
        $subscriptionActionMock->method('handle')->willReturn(true);

        $subscription = $this->createPartialMock(Subscription::class, ['getAction']);
        $subscription->method('getAction')->willReturn($subscriptionActionMock);

        $processor = new ProcessSubscriptionJob($subscription);
        $processor->handle();

        Event::assertDispatched(TransactionSuccessEvent::class, function ($e) use ($subscription) {
            return $e->getSubscription()->is($subscription);
        });
    }

    /**
     * @throws Exception
     */
    public function testTransactionFailedListenerWillSuspendSubscriptionWhenMaxRetriesReached(): void
    {
        TestTime::freeze(Carbon::parse('2021-01-01 00:00:00'));

        $subscriptionActionMock = $this->createMock(SubscriptionActionContract::class);
        $subscriptionActionMock->method('handle')->willReturn(false);

        $mockBuilder = Mockery::mock(Builder::class);
        $mockBuilder->shouldReceive('count')->andReturn(1);

        $subscription = Mockery::mock(Subscription::class);
        $subscription->shouldReceive('getAction')->andReturn($subscriptionActionMock);
        $subscription->shouldReceive('getAttribute')->with('max_retries')->andReturn(1);
        $subscription->shouldReceive('failedPaymentsLastMonth')->andReturn($mockBuilder);
        $subscription->shouldReceive('suspend')->once();

        event(new TransactionFailedEvent($subscription));
    }

    public function testTransactionSuccessEventWillResumeSubscriptionOnSuccessfulTransaction(): void
    {
        TestTime::freeze(Carbon::parse('2021-01-01 00:00:00'));

        $subscription = Mockery::mock(Subscription::class);
        $subscription->shouldReceive('getAttribute')->with('status')->andReturn(Status::SUSPENDED);
        $subscription->shouldReceive('resume')->once();

        event(new TransactionSuccessEvent($subscription));
    }
}