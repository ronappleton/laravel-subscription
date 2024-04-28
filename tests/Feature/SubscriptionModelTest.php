<?php

declare(strict_types=1);

namespace Tests\Feature;

use Appleton\Subscriptions\Contracts\SubscriptionAction;
use Appleton\Subscriptions\Models\Subscription;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Appleton\Subscriptions\Exceptions\Subscription as SubscriptionException;
use Tests\TestCase;

class SubscriptionModelTest extends TestCase
{
    use DatabaseMigrations;

    public function testSubscriptionCanBeCreated(): void
    {
        $subscription = Subscription::factory()->create();

        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id]);
    }

    public function testSubscriptionActionIsValid(): void
    {
        $subscription = Subscription::factory()->create();

        $this->assertInstanceOf(SubscriptionAction::class, $subscription->getAction());
    }

    public function testSubscriptionActionIsInvalid(): void
    {
        $subscription = Subscription::factory()->state(['action_class' => 'InvalidClass'])->create();

        $this->expectException(SubscriptionException::class);

        $subscription->getAction();
    }

    public function testSubscriptionCanBePaused(): void
    {
        $subscription = Subscription::factory()->state(['allow_pause' => true, 'max_pauses' => 5])->create();

        $this->assertTrue($subscription->canPause());
    }

    public function testSubscriptionCannotBePaused(): void
    {
        $subscription = Subscription::factory()->state(['allow_pause' => false])->create();

        $this->assertFalse($subscription->canPause());
    }

    public function testSubscriptionEndDateIsPopulated(): void
    {
        $subscription = Subscription::factory()->create();

        $subscription->populateEndDate();

        $this->assertNotNull($subscription->ends_at);
    }

    public function testSubscriptionStatusScopeWorks(): void
    {
        $subscription = Subscription::factory()->state(['status' => Status::ACTIVE])->create();

        $this->assertEquals(1, Subscription::status(Status::ACTIVE)->count());
    }
}