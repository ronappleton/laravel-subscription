<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Concerns;

use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Models\Subscription;
use Carbon\Carbon;
use Tests\TestCase;

class HasSuspendTest extends TestCase
{
    public function testCanSuspendWithCallback(): void
    {
        $subscription = new Subscription();
        $subscription->status = Status::ACTIVE;
        $subscription->activated_at = Carbon::now();
        $subscription->suspended_at = null;
        $subscription->ended_at = null;

        $this->assertTrue($subscription->canSuspend(fn($subscription) => true));
    }
}