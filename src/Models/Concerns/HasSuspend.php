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
 * @property ?Carbon $suspended_at
 */
trait HasSuspend
{
    private array $suspendableStatuses = [
        Status::ACTIVE,
        Status::PAUSED,
    ];

    public function suspend(): void
    {
        if (!$this->canSuspend()) {
            return;
        }

        $this->suspended_at = Carbon::now();
        $this->status = Status::SUSPENDED;
        $this->save();
    }

    public function isSuspended(): bool
    {
        return $this->status === Status::SUSPENDED;
    }

    /**
     * A custom callback to determine if the subscription can be suspended for example a role or permission check.
     */
    public function canSuspend(?Closure $callback = null): bool
    {
        $baseChecks = !is_null($this->activated_at)
            && is_null($this->ended_at)
            && is_null($this->suspended_at)
            && in_array($this->status, $this->suspendableStatuses, true);

        if (is_callable($callback)) {
            return $baseChecks && $callback($this);
        }

        return $baseChecks;
    }
}