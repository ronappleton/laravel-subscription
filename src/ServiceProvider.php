<?php

declare(strict_types=1);

namespace Appleton\Subscriptions;

use Appleton\Subscriptions\Events\TransactionFailedEvent;
use Appleton\Subscriptions\Events\TransactionSuccessEvent;
use Appleton\Subscriptions\Listeners\TransactionFailedListener;
use Appleton\Subscriptions\Listeners\TransactionSuccessListener;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Event;
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

        $this->bindJobs();
    }

    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/subscriptions.php' => config_path('subscriptions.php'),
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('subscriptions:process')->daily();
        });

        $this->listenForEvents();
    }

    private function bindJobs(): void
    {
        $this->app->bind(
            Jobs\Contracts\ProcessSubscriptionJob::class,
            Jobs\ProcessSubscriptionJob::class
        );
    }

    private function listenForEvents(): void
    {
        Event::listen(
            TransactionSuccessEvent::class,
            TransactionSuccessListener::class,
        );

        Event::listen(
            TransactionFailedEvent::class,
            TransactionFailedListener::class,
        );
    }
}