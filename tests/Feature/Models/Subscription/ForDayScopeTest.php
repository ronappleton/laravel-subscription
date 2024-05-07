<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Subscription;

use Appleton\Subscriptions\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class ForDayScopeTest extends TestCase
{
    use DatabaseMigrations;

    public function testForDayScopeWithDay(): void
    {
        TestTime::freeze(Carbon::parse('2021-01-01'));

        Subscription::factory(2)->create([
            'fixed_day_of_month' => 1,
        ]);

        Subscription::factory(3)->create([
            'fixed_day_of_month' => 2,
        ]);

        $subscriptions = Subscription::forDay()->get();

        $this->assertCount(2, $subscriptions);
    }
}