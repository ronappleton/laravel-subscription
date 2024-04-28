<?php

declare(strict_types=1);

namespace Tests\Feature\Processor;

use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Jobs\ProcessSubscriptionJob;
use Appleton\Subscriptions\Models\Subscription;
use Appleton\Subscriptions\Processors\Processor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Queue;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class ProcessorTest extends TestCase
{
    use DatabaseMigrations;

    public function testWarningsAreProcessed(): void
    {
        TestTime::freeze(Carbon::parse('2021-01-01'));

        Queue::fake();

        Subscription::factory()->create([
            'status' => Status::ACTIVE,
            'advanced_warning_days' => 3,
            'fixed_day_of_month' => 4,
        ]);

        $processor = new Processor();

        $processor->processWarnings();

        Queue::assertPushed(ProcessSubscriptionJob::class);
    }
}