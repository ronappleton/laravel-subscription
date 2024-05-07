<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Subscription;

use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Models\Subscription;
use Appleton\Subscriptions\Models\SubscriptionLog;
use Carbon\Carbon;
use Database\Factories\SubscriptionFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class StateTest extends TestCase
{
    use DatabaseMigrations;

    public function testPause(): void
    {
        /** @var Subscription $subscription */
        $subscription = SubscriptionFactory::new()->create([
            'allow_pause' => true,
            'status' => Status::ACTIVE,
            'activated_at' => '2024-03-22 01:33:33',
            'paused_at' => null,
            'ended_at' => null,
        ]);

        $this->assertTrue($subscription->canPause());

        $subscription->pause();

        $this->assertNotNull($subscription->paused_at);
        $this->assertTrue($subscription->isPaused());
    }

    public function testResume(): void
    {
        /** @var Subscription $subscription */
        $subscription = SubscriptionFactory::new()->create([
            'status' => Status::PAUSED,
            'paused_at' => '2024-03-22 01:33:33',
            'activated_at' => '2024-03-22 01:33:33',
            'resumed_at' => null,
        ]);

        $subscription->resume();

        $this->assertNull($subscription->paused_at);
        $this->assertTrue($subscription->isActivated());
    }

    public function testCancel(): void
    {
        /** @var Subscription $subscription */
        $subscription = SubscriptionFactory::new()->create([
            'allow_cancel' => true,
            'status' => Status::ACTIVE,
            'activated_at' => '2024-03-22 01:33:33',
            'paused_at' => null,
            'ended_at' => null,
            'cancelled_at' => null,
        ]);

        $this->assertTrue($subscription->canCancel());

        $subscription->cancel();

        $this->assertNotNull($subscription->cancelled_at);
        $this->assertTrue($subscription->isCancelled());
    }

    public function testEnd(): void
    {
        /** @var Subscription $subscription */
        $subscription = SubscriptionFactory::new()->create([
            'status' => Status::ACTIVE,
            'activated_at' => '2024-03-22 01:33:33',
            'ended_at' => null,
        ]);

        $subscription->end();

        $this->assertNotNull($subscription->ended_at);
        $this->assertTrue($subscription->isEnded());
    }

    public function testSuspend(): void
    {
        /** @var Subscription $subscription */
        $subscription = SubscriptionFactory::new()->create([
            'status' => Status::ACTIVE,
            'activated_at' => '2024-03-22 01:33:33',
            'suspended_at' => null,
            'ended_at' => null,
        ]);

        $subscription->suspend();

        $this->assertNotNull($subscription->suspended_at);
        $this->assertTrue($subscription->isSuspended());
    }

    public function testIsDue(): void
    {
        TestTime::freeze(Carbon::parse('2024-03-22 01:33:33'));

        /** @var Subscription $subscription */
        $subscription = SubscriptionFactory::new()->create([
            'status' => Status::ACTIVE,
            'fixed_day_of_month' => 22,
        ]);

        $this->assertTrue($subscription->isDue());
    }

    public function testActivate(): void
    {
        /** @var Subscription $subscription */
        $subscription = SubscriptionFactory::new()->create([
            'status' => Status::INACTIVE,
        ]);

        $subscription->activate();

        $this->assertNotNull($subscription->activated_at);
        $this->assertTrue($subscription->isActivated());
    }

    public function testCanChangeFixedDate(): void
    {
        /** @var Subscription $subscription */
        $subscription = SubscriptionFactory::new()->create([
            'allow_fixed_day_change' => true,
        ]);

        $this->assertTrue($subscription->isFixedDayChangeAllowed());
    }

    public function testCannotResumeIfNotPaused(): void
    {
        /** @var Subscription $subscription */
        $subscription = SubscriptionFactory::new()->create([
            'status' => Status::ENDED,
        ]);

        $subscription->resume();

        $this->assertEquals(Status::ENDED, $subscription->status);
    }

    public function testCannotCancelIfNotAllowed(): void
    {
        /** @var Subscription $subscription */
        $subscription = SubscriptionFactory::new()->create([
            'allow_cancel' => false,
            'status' => Status::ACTIVE,
        ]);

        $subscription->cancel();

        $this->assertEquals(Status::ACTIVE, $subscription->status);
    }

    /**
     * @param array<string, bool> $attributes
     */
    #[DataProvider('cannotEndStatusProvider')]
    public function testCannotEndIfUnEndAble(Status $status, array $attributes): void
    {
        /** @var Subscription $subscription */
        $subscription = SubscriptionFactory::new()->create($attributes);

        $subscription->end();

        $this->assertEquals($status, $subscription->status);
    }

    /**
     * @return array<int, array<string, Status|array<string, bool|string>>>
     */
    public static function cannotEndStatusProvider(): array
    {
        return [
            [
                'status' => Status::ENDED,
                'attributes' => [
                    'status' => 'ended',
                    'allow_pause' => true,
                    'ended_at' => '2024-03-22 01:33:33',
                ],
            ],
            [
                'status' => Status::CANCELLED,
                'attributes' => [
                    'status' => 'cancelled',
                    'allow_cancel' => true,
                    'cancelled_at' => '2024-03-22 01:33:33',
                ]
            ],
            [
                'status' => Status::SUSPENDED,
                'attributes' => [
                    'status' => 'suspended',
                    'suspended_at' => '2024-03-22 01:33:33',
                ]
            ],
        ];
    }

    public function testCannotPauseIfCannotPause(): void
    {
        /** @var Subscription $subscription */
        $subscription = SubscriptionFactory::new()->create([
            'allow_pause' => false,
            'status' => Status::ACTIVE,
        ]);

        $subscription->pause();

        $this->assertEquals(Status::ACTIVE, $subscription->status);
    }

    public function testCannotPauseIfNotActive(): void
    {
        /** @var Subscription $subscription */
        $subscription = SubscriptionFactory::new()->create([
            'allow_pause' => true,
            'status' => Status::ENDED,
        ]);

        $subscription->pause();

        $this->assertEquals(Status::ENDED, $subscription->status);
    }

    public function testCannotActivateIfActive(): void
    {
        /** @var Subscription $subscription */
        $subscription = SubscriptionFactory::new()->create([
            'status' => Status::ACTIVE,
        ]);

        $subscription->activate();

        $this->assertEquals(Status::ACTIVE, $subscription->status);
    }

    public function testCannotSuspendIfNotActive(): void
    {
        /** @var Subscription $subscription */
        $subscription = SubscriptionFactory::new()->create([
            'status' => Status::ENDED,
        ]);

        $subscription->suspend();

        $this->assertEquals(Status::ENDED, $subscription->status);
    }

    public function testCanResumed(): void
    {
        /** @var Subscription $subscription */
        $subscription = SubscriptionFactory::new()->create([
            'status' => Status::PAUSED,
            'activated_at' => '2024-03-22 01:33:33',
            'paused_at' => '2024-03-22 01:33:33',
            'resumed_at' => null,
        ]);

        $subscription->resume();

        $this->assertTrue($subscription->isResumed());
    }
}