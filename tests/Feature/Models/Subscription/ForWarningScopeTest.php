<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Subscription;

use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class ForWarningScopeTest extends TestCase
{
    use DatabaseMigrations;

    public function testForWarningDoesNotIncludeASubscriptionThatIsNotActive(): void
    {
        TestTime::freeze(Carbon::parse('2021-01-01'));

        Subscription::factory()->create([
            'status' => Status::PAUSED,
            'advanced_warning_days' => 3,
            'fixed_day_of_month' => Carbon::now()->addDays(3)->day,
        ]);

        $subscriptions = Subscription::forWarning()->get();

        $this->assertCount(0, $subscriptions);
    }

    public function testForWarningDoesNotIncludeASubscriptionWhenItIsBeforeAdvancedWarningDays(): void
    {
        TestTime::freeze(Carbon::parse('2021-01-01'));

        Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'advanced_warning_days' => 3,
            'fixed_day_of_month' => Carbon::now()->addDays(4)->day,
        ]);

        $subscriptions = Subscription::forWarning()->get();

        $this->assertCount(0, $subscriptions);
    }

    public function testForWarningDoesNotIncludeASubscriptionWhenItIsAfterAdvancedWarningDays(): void
    {
        TestTime::freeze(Carbon::parse('2021-01-01'));

        Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'advanced_warning_days' => 3,
            'fixed_day_of_month' => Carbon::now()->addDays(2)->day,
        ]);

        $subscriptions = Subscription::forWarning()->get();

        $this->assertCount(0, $subscriptions);
    }

    public function testForWarningDoesIncludesASubscriptionBecauseItIsAdvancedWarningDays(): void
    {
        TestTime::freeze(Carbon::parse('2021-01-01'));

        Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'advanced_warning_days' => 3,
            'fixed_day_of_month' => 4,
        ]);

        $subscriptions = Subscription::forWarning()->get();

        $this->assertCount(1, $subscriptions);
    }
}