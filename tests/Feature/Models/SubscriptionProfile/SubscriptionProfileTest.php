<?php

declare(strict_types=1);

namespace Tests\Feature\Models\SubscriptionProfile;

use Appleton\Subscriptions\Models\Subscription;
use Appleton\Subscriptions\Models\SubscriptionProfile;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class SubscriptionProfileTest extends TestCase
{
    use DatabaseMigrations;

    public function testGetUser(): void
    {
        config()->set('subscriptions.user_model', Subscription::class);

        /** @var Subscription $user */
        $user = Subscription::factory()->create();

        $subscriptionProfile = SubscriptionProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(Subscription::class, $subscriptionProfile->user);
    }
}