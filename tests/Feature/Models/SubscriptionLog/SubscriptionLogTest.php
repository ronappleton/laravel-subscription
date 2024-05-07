<?php

declare(strict_types=1);

namespace Tests\Feature\Models\SubscriptionLog;

use Appleton\Subscriptions\Enums\PaymentStatus;
use Appleton\Subscriptions\Models\Subscription;
use Appleton\Subscriptions\Models\SubscriptionLog;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class SubscriptionLogTest extends TestCase
{
    use DatabaseMigrations;

    public function testStatusScope(): void
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'status' => 'active',
        ]);

        SubscriptionLog::create([
            'subscription_id' => $subscription->id,
            'amount' => 100,
            'status' => PaymentStatus::PAID,
            'created_at' => now(),
        ]);

        SubscriptionLog::create([
            'subscription_id' => $subscription->id,
            'amount' => 100,
            'status' => PaymentStatus::UNPAID,
            'created_at' => now(),
        ]);

        $this->assertCount(1, SubscriptionLog::status(PaymentStatus::PAID)->get());
    }

    public function testAgeInDaysScope(): void
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'status' => 'active',
        ]);

        SubscriptionLog::create([
            'subscription_id' => $subscription->id,
            'amount' => 100,
            'status' => PaymentStatus::PAID,
            'created_at' => now(),
        ]);

        SubscriptionLog::create([
            'subscription_id' => $subscription->id,
            'amount' => 100,
            'status' => PaymentStatus::UNPAID,
            'created_at' => now()->subDays(2),
        ]);

        $this->assertCount(1, SubscriptionLog::ageInDays(1)->get());
    }
}