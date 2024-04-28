<?php

declare(strict_types=1);

namespace Appleton\Subscriptions;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/subscriptions.php', 'subscriptions');

        $this->app->bind(
            Contracts\SubscriptionProcessor::class,
            Processors\Processor::class
        );

        $this->commands([
            Console\Commands\ProcessSubscriptionsCommand::class,
        ]);

        $this->bindEnums();
        $this->bindJobs();
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/subscriptions.php' => config_path('subscriptions.php'),
        ]);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('subscriptions:process')->daily();
        });

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Bind the enums to their interfaces.
     *
     * This is done to allow extension of the enums in the future.
     */
    private function bindEnums(): void
    {
        $this->app->bind(Enums\Contracts\Status::class, Enums\Status::class);
        $this->app->bind(Enums\Contracts\Frequency::class, Enums\PaymentFrequency::class);
        $this->app->bind(Enums\Contracts\PausePeriod::class, Enums\PausePeriod::class);
    }

    private function bindJobs(): void
    {
        $this->app->bind(
            Jobs\Contracts\ProcessSubscriptionJob::class,
            Jobs\ProcessSubscriptionJob::class
        );
    }
}