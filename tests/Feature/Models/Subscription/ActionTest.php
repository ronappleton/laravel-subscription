<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Subscription;

use Appleton\Subscriptions\Contracts\SubscriptionAction;
use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Models\Subscription;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Appleton\Subscriptions\Exceptions\SubscriptionAction as SubscriptionActionException;
use Tests\TestCase;

class ActionTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @throws SubscriptionActionException
     * @throws BindingResolutionException
     */
    public function testAction(): void
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'allow_pause' => true,
            'status' => Status::ACTIVE,
        ]);

        $action = new class implements SubscriptionAction {
            public function handle(Subscription $subscription): bool
            {
                return true;
            }
        };

        $subscription->action_class = $action::class;
        $subscription->save();

        $this->assertInstanceOf(SubscriptionAction::class, $subscription->getAction());
    }

    /**
     * @throws BindingResolutionException
     */
    public function testExceptionThrownWhenActionClassDoesNotExist(): void
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'allow_pause' => true,
            'status' => Status::ACTIVE,
        ]);

        $subscription->action_class = 'NonExistentClass';
        $subscription->save();

        $this->expectException(SubscriptionActionException::class);
        $this->expectExceptionMessage('Action NonExistentClass not found');
        $subscription->getAction();
    }

    /**
     * @throws BindingResolutionException
     */
    public function testExceptionThrownWhenActionClassDoesNotImplementSubscriptionAction(): void
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'allow_pause' => true,
            'status' => Status::ACTIVE,
        ]);

        $action = new class {
            public function handle(Subscription $subscription): bool
            {
                return true;
            }
        };

        $subscription->action_class = $action::class;
        $subscription->save();

        $this->expectException(SubscriptionActionException::class);
        $message = sprintf('Action %s must implement %s', $action::class, SubscriptionAction::class);
        $this->expectExceptionMessage($message);
        $subscription->getAction();
    }
}