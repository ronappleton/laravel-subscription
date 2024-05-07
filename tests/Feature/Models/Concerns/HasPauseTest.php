<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Concerns;

use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Models\Subscription;
use Carbon\Carbon;
use Tests\TestCase;

class HasPauseTest extends TestCase
{
    public function testCanPauseWithCallback(): void
    {
        $subscription = new Subscription();
        $subscription->status = Status::ACTIVE;
        $subscription->activated_at = Carbon::now();
        $subscription->paused_at = null;
        $subscription->ended_at = null;
        $subscription->allow_pause = true;

        $this->assertTrue($subscription->canPause(fn() => true));
    }
}