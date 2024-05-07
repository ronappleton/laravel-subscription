<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Subscription;

use Appleton\Subscriptions\Enums\PaymentStatus;
use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Models\Subscription;
use Appleton\Subscriptions\Models\SubscriptionLog;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class RelationTest extends TestCase
{
    use DatabaseMigrations;

    public function testPayerRelation(): void
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'status' => Status::ACTIVE,
        ]);

        $payer = SubscriptionLog::create([
            'subscription_id' => $subscription->id,
            'amount' => 100,
            'status' => PaymentStatus::PAID,
            'created_at' => now(),
        ]);

        $subscription->payer_type = SubscriptionLog::class;
        $subscription->payer_id = $payer->id;

        $this->assertInstanceOf(SubscriptionLog::class, $subscription->payer);
    }

    public function testPayeeRelation(): void
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'status' => Status::ACTIVE,
        ]);

        $payee = SubscriptionLog::create([
            'subscription_id' => $subscription->id,
            'amount' => 100,
            'status' => PaymentStatus::PAID,
            'created_at' => now(),
        ]);

        $subscription->payee_type = SubscriptionLog::class;
        $subscription->payee_id = $payee->id;

        $this->assertInstanceOf(SubscriptionLog::class, $subscription->payee);
    }
}