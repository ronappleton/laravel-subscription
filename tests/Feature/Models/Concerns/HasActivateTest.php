<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Concerns;

use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Models\Subscription;
use Tests\TestCase;

class HasActivateTest extends TestCase
{
    public function testCanActivateWithCallback(): void
    {
        $subscription = new Subscription();
        $subscription->status = Status::INACTIVE;
        $subscription->activated_at = null;
        $subscription->cancelled_at = null;
        $subscription->ended_at = null;

        $this->assertTrue($subscription->canActivate(fn($subscription) => true));
    }
}