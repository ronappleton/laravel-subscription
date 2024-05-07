<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Models\Concerns;

use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Models\Subscription;
use Carbon\Carbon;
use Closure;

/**
 * @mixin Subscription
 * @mixin HasStatus
 *
 * @property ?Carbon $activated_at
 */
trait HasActivate
{
    private array $activatableStatuses = [
        Status::INACTIVE,
    ];

    public function activate(): void
    {
        if (!$this->canActivate()) {
            return;
        }

        $this->activated_at = Carbon::now();
        $this->status = Status::ACTIVE;
        $this->save();
    }

    public function isActivated(): bool
    {
        return $this->status === Status::ACTIVE;
    }

    /**
     * A custom callback to determine if the subscription can be activated for example a role or permission check.
     */
    public function canActivate(?Closure $callback = null): bool
    {
        $baseChecks = is_null($this->activated_at)
            && is_null($this->cancelled_at)
            && is_null($this->ended_at)
            && in_array($this->status, $this->activatableStatuses, true);

        if (is_callable($callback)) {
            return $baseChecks && $callback($this);
        }

        return $baseChecks;
    }
}