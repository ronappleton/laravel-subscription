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
 * @property ?Carbon $cancelled_at
 */
trait HasCancel
{
    private array $cancelableStatuses = [
        Status::ACTIVE,
        Status::PAUSED,
    ];

    public function cancel(): void
    {
        if (!$this->canCancel()) {
            return;
        }

        $this->cancelled_at = Carbon::now();
        $this->status = Status::CANCELLED;
        $this->save();
    }

    public function isCancelled(): bool
    {
        return $this->status === Status::CANCELLED;
    }

    /**
     * A custom callback to determine if the subscription can be cancelled for example a role or permission check.
     */
    public function canCancel(?Closure $callback = null): bool
    {
        $baseChecks = !is_null($this->activated_at)
            && is_null($this->paused_at)
            && is_null($this->ended_at)
            && $this->allow_cancel
            && is_null($this->cancelled_at)
            && in_array($this->status, $this->cancelableStatuses, true);

        if (is_callable($callback)) {
            return $baseChecks && $callback($this);
        }

        return $baseChecks;
    }
}