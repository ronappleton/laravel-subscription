<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Concerns;

use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Models\Subscription;
use Carbon\Carbon;
use Tests\TestCase;

class HasResumeTest extends TestCase
{
    public function testCanResumeWithCallback(): void
    {
        $subscription = new Subscription();
        $subscription->status = Status::PAUSED;
        $subscription->activated_at = Carbon::now();
        $subscription->paused_at = Carbon::now();
        $subscription->resumed_at = null;

        $this->assertTrue($subscription->canResume(fn($subscription) => true));
    }
}