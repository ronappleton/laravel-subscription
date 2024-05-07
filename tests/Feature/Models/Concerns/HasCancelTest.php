<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Concerns;

use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Models\Subscription;
use Carbon\Carbon;
use Tests\TestCase;

class HasCancelTest extends TestCase
{
    public function testCanCancelWithCallback(): void
    {
        $subscription = new Subscription();
        $subscription->status = Status::ACTIVE;
        $subscription->activated_at = Carbon::now();
        $subscription->cancelled_at = null;
        $subscription->ended_at = null;
        $subscription->allow_cancel = true;

        $this->assertTrue($subscription->canCancel(fn($subscription) => true));
    }
}